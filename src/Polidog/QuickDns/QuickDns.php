<?php

namespace Polidog\QuickDns;

use Polidog\QuickDns\Exception\QuickDnsException;
use Polidog\QuickDns\Domain\Domain;
use Polidog\QuickDns\Storage\StorageInterface;

/**
 * DNSサーバー
 * @property StorageAbstract $Storage ドライバークラス
 * @property socket $socket DNS問い合わせ用のソケット
 * @property socket $consoleSocket コンソール接続用のソケット
 */
class QuickDns {

	private static $isInit = false;
	private $socket;
	private $consoleSocket;
	private $Storage;
	private $config;

	private static $dnsTypes = array(
		"A" => 1,
		"NS" => 2,
		"CNAME" => 5,
		"SOA" => 6,
		"WKS" => 11,
		"PTR" => 12,
		"HINFO" => 13,
		"MX" => 15,
		"TXT" => 16,
		"RP" => 17,
		"SIG" => 24,
		"KEY" => 25,
		"LOC" => 29,
		"NXT" => 30,
		"AAAA" => 28,
		"CERT" => 37,
		"A6" => 38,
		"AXFR" => 252,
		"IXFR" => 251,
		"*" => 255
	);

	/**
	 * コンストラクタ
	 * @param array $config
	 */
	public function __construct($config = null) {
		
		$defaultConfig = $this->getDefaultConfig();
		if (empty($config)) {
			$config = $defaultConfig();
		}
		$this->set($config);
	}

	/**
	 * 設定をセットする
	 * @param mixed $key
	 * @param mixed $value
	 * @return \Polidog\QuickDns\QuickDns
	 * @throws QuickDnsException
	 */
	public function set($key, $value = null) {

		if (self::$isInit == true) {
			throw new QuickDnsException('is callded init method');
		}

		if (is_array($key)) {
			foreach ($key as $k => $v) {
				$this->set($k, $v);
			}
		} else {
			if ($key != 'storage' && $key != 'storage_config')
				$this->config[$key] = $value;
		}
		return $this;
	}

	/**
	 * 設定の取得
	 * @param string $key
	 * @return array|boolean
	 */
	public function get($key = null) {
		if (is_null($key)) {
			return $this->config;
		} else if (isset($this->config[$key])) {
			return $this->config[$key];
		}
		return false;
	}

	/**
	 * 初期化処理
	 * @param closer $callback コールバッック処理用のfunction
	 * @return mixed
	 * @throws QuickDnsException
	 */
	public function init($callback = null) {

		if (self::$isInit == true)
			return;

		if (empty($callback)) {
			throw new QuickDnsException();
		}

		if (is_array($callback)) {
			$isCall = false;
			foreach ($callback as $call) {
				if ($this->isClosure($call)) {
					$isCall = true;
					$call();
				}
			}

			if (!$isCall) {
				throw new QuickDnsException();
			}
		} else if ($this->isClosure($callback)) {
			$callback();
		} else {
			throw new QuickDnsException();
		}

		self::$isInit = true;

	}

	/**
	 * DNSサーバを起動する
	 * @param int $port 受け付けるポート番号
	 */
	public function listen($port = null) {

		if (!self::$isInit) {
			$_this = $this;
			$this->init(function() use ($_this) {
				// デフォルト設定
				$_this->set(QuickDns::BASE_CONFIG);
			});
		}

		if ($port == null)
			$port = $this->get('port');
		$localip = $this->get('localip');

		$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if ($this->socket < 0) {
			throw new QuickDnsException('socket no create');
		}
		if (socket_bind($this->socket, $localip, $port) == false)
			throw new QuickDnsException('socket no bind');

		$this->output("start QuickDns server", "info");
		while (true) {
			try {
				
				$len = socket_recvfrom($this->socket, $buffer, 1024 * 4, 0, $ip, $port);
				if ($len > 0) {
					$this->lookup($buffer, $ip, $port);
				}
			} catch (QuickDnsException $QuickDns) {
				if (!empty($this->socket)) {
					socket_close($this->socket);
				}
				$this->output($QuickDns->getMessage(), 'error');
			}
		}
	}
	
	/**
	 * ストレージを取得する
	 * @return StorageInterface
	 */
	public function getStorage() {
		return $this->Storage;
	}
	
	/**
	 * ストレージをセットする
	 * @param \Polidog\QuickDns\Storage\StorageInterface $storage
	 */
	public function setStorage(StorageInterface $storage) {
		$this->Storage = $storage;
	}

	/**
	 * DNSに問い合わせ処理をする
	 * @param string $buffer
	 * @param string $client_ip
	 * @param string $client_port
	 * @return null
	 * @throws QuickDnsException
	 */
	private function lookup($buffer, $client_ip, $client_port) {
		// ドメインの抜き出し
		$domainName = false;
		$tmp = substr($buffer, 12);
		$length = strlen($tmp);

		for ($i = 0; $i < $length; $i++) {
			$char = ord($tmp[$i]);
			if ($char == 0) {
				break;
			}
			$domainName .= substr($tmp, $i + 1, $char) . ".";
			$i += $char;
		}
		$domainName = rtrim($domainName, ".");
		
		$i += 2;
		$queryType = array_search((string) ord($tmp[$i]), $this->getTypes());
		if ($queryType != "A" && $queryType != "CNAME") {
			// Aレコード以外はムリポ
			return $this->lookupExternal($domainName, $buffer, $client_ip, $client_port, false);
		}
		
		$this->output("question domain:{$domainName}", "info");
		$this->output("query type:{$queryType}", "info");
		
		$domainObject = $this->Storage->searchDomain($domainName);
		
		
		if (!$domainObject->is()) {
			// ローカル環境で名前解決出来ない場合はそとに行く
			$this->output("call lookupExternal", "info");
			return $this->lookupExternal($domainName, $buffer, $client_ip, $client_port);
		}
		
		$this->output("ip address:{$domainObject->ipAddress}", "info");

		$answer = $buffer[0] . $buffer[1] . chr(129) . chr(128) . $buffer[4] . $buffer[5] . $buffer[4] . $buffer[5] . chr(0) . chr(0) . chr(0) . chr(0);
		$answer .= substr($buffer, 12);
		$answer .= chr(192) . chr(12);
		$answer .= chr(0) . chr(1) . chr(0) . chr(1) . chr(0) . chr(0) . chr(0) . chr(60) . chr(0) . chr(4);
		$answer .= $domainObject->exportIpAddressBinary();

		if (socket_sendto($this->socket, $answer, strlen($answer), 0, $client_ip, $client_port) === false) {
			throw new QuickDnsException("not found");
		}
	}

	/**
	 * 外部のDNSに問い合わせに行く
	 * @param string $domainName
	 * @param string $buffer
	 * @param string $client_ip
	 * @param string $client_port
	 * @throws QuickDnsException
	 */
	private function lookupExternal($domainName, $buffer, $client_ip, $client_port, $useCache = true) {
		$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		$externalDnsIp = $this->get('external_dns');
		if (!$externalDnsIp) {
			throw new QuickDnsException("no external dns server ip address");
		}

		// 別DNSの問い合わせ
		socket_connect($socket, $externalDnsIp, 53);
		socket_send($socket, $buffer, strLen($buffer), 0);
		$resultBuffer = socket_read($socket, 1024 * 8);
		socket_close($socket);

		// ipアドレスの取得
		$tmp = substr($resultBuffer, -4, 4);
		$length = strlen($tmp);
		$ip = null;
		for ($i = 0; $i < $length; $i++) {
			$char = ord($tmp[$i]);
			$ip .= $char . ".";
		}
		$ip = rtrim($ip, ".");

		// キャッシュする
		if (!empty($ip) && $useCache) {
			$this->Storage->save(new Domain(rtrim($domainName,','),$ip,3600));
		}

		// クライアントにDNSの結果を渡す
		socket_sendto($this->socket, $resultBuffer, strlen($resultBuffer), 0, $client_ip, $client_port);
	}

	
	/**
	 * 出力処理を行う
	 * @param string $message
	 * @param string $prefix
	 * @return void
	 */
	private function output($message, $prefix = "") {
		if (!$this->get('stdout')) {
			return;
		}

		$output = "";
		if (!empty($prefix)) {
			$output = "[{$prefix}]";
		}
		$output .= $message . "\n";
		echo $output;
	}

	
	/**
	 * DNSの問い合わせタイプを取得する
	 * @return array
	 */
	private function getTypes() {
		return static::$dnsTypes;
	}
	
	
	/**
	 * 設定ファイルを取得する
	 * @return closuer
	 */
	final public function getDefaultConfig() {
		
		$defaultConfig = array();
		return function () use($defaultConfig) {
			if (empty($defaultConfig)) {
				$defaultConfig = array(
					'localip' => '127.0.0.1',
					'port' => '53',
					'console_port' => '10054',
					'storage' => array(
						'type' => 'List',
						'data' => array(
							'www.polidog.jp' => '133.242.145.155',
						),
					),
					'external_dns' => '8.8.8.8',
					'cache_expir' => 3600,
				);
			}
			return $defaultConfig;
		};
	}
	
	/**
	 * クロージャーか判定する
	 * @param $closuer
	 * @return boolean
	 */
	private function isClosure($closuer) {
		return ( is_object($closuer) && $closuer instanceof \Closure );
	}	

}