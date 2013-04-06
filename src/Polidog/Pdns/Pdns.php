<?php
namespace Polidog\Pdns;
use Polidog\Pdns\PdnsException;

/**
 * DNSサーバー
 * @property Polidog\Pdns\Driver\DriverAbstract $Driver ドライバークラス
 */
class Pdns {
	private static $isInit = false;
	private $socket;
	
	private $Driver;
	
	private $settings = array(
		'localip'	=> '127.0.0.1',
		'port'		=> '53',
		'driver'	=> 'List',
		'driver_config' => array(),
		'external_dns' => '8.8.8.8',
	);
	
	
	public function set($key, $value = null) {
		
		if (self::$isInit == true) {
			throw new PdnsException('is callded init method');
		}
		
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				$this->set($k,$v);
			}
		} else {
			$this->settings[$key] = $value;
		}
		return $this;
	}
	
	public function get($key = null) {
		if (is_null($key)) {
			return $this->settings;
		} else if (isset($this->settings[$key])) {
			return $this->settings[$key];
		}
		return false;
	}
	
	public function init($callback = null) {
		
		if (self::$isInit == true) return;
		
		if (empty($callback) ) {
			throw new PdnsException();
		}
		
		if (is_array($callback)) {
			$isCall = false;
			foreach ($callback as $call) {
				if ( $this->isClosure($call) ) {
					$isCall = true;
					$call();
				}
			}
			
			if (!$isCall) {
				throw new PdnsException();
			}
			
		} else if ($this->isClosure($callback)) {
			$callback();
		} else {
			throw new PdnsException();
		}
		
		self::$isInit = true;
		
		// ストレージのインスタンス化
		$this->loadDriver($this->get());
	}
	
	
	/**
	 * DNSサーバを起動する
	 * @param int $port 受け付けるポート番号
	 */
	public function listen($port = null) {
		
		if (!self::$isInit) {
			throw new PdnsException("dns no setup");
		}
		
		if ($port == null) $port = $this->get('port');
		$localip = $this->get('localip');
		
		$this->socket = socket_create(AF_INET,SOCK_DGRAM, SOL_UDP);
		if ($this->socket < 0) {
			throw new PdnsException('socket no create');
		}
		if (socket_bind($this->socket, $localip, $port) == false) throw new PdnsException('socket no bind');
		
		$this->output("start pdns server","info");
		while(true) {
			try {
				$len = socket_recvfrom($this->socket, $buffer, 1024*4, 0, $ip, $port);
				if ($len > 0) {
					$this->lookup($buffer, $ip, $port);
				}
			} catch (PdnsException $pdns) {
				if (!empty($this->socket)) {
					socket_close($this->socket);
				}
				$this->output($pdns->getMessage(),'error');
			}
		}
	}
	
	
	/**
	 * クロージャーか判定する
	 * @param $closuer
	 * @return boolean
	 */
	private function isClosure($closuer) {
		return ( is_object($closuer) && $closuer instanceof \Closure );
	}
	
	/**
	 * ドライバーをロードする
	 * @param array $config
	 * @throws PdnsException
	 */
	private function loadDriver($config) {
		if ( !isset($config['driver']) ) {
			throw new PdnsException("no select driver");
		}
		$driver_config = array();
		if (isset($config['driver_config'])) {
			$driver_config = $config['driver_config'];
		}
		$className = __NAMESPACE__."\\Driver\\".$config['driver'].'Driver';
		if (!class_exists($className)) {
			throw new PdnsException("driver class not found");
		}
		$this->Driver = new $className($driver_config);
	}
	
	
	private function lookup($buffer, $client_ip, $client_port) {
		
		// ドメインの抜き出し
		$domain = false;
		$tmp = substr($buffer, 12);
		$length = strlen($tmp);
		
		for ($i = 0; $i < $length; $i++) {
			$char = ord($tmp[$i]);
			if ($char == 0) {
				break;
			}
			$domain .= substr($tmp, $i+1, $char).".";
			$i += $char;
		}
		$i += 2;
		$queryType = array_search((string)ord($tmp[$i]), $this->getTypes() );
		if ($queryType != "A" && $queryType != "CNAME") {
			// Aレコード以外はムリポ
			return $this->lookupExternal($domain, $buffer, $client_ip, $client_port);
		}
		
		$this->output("question domain:{$domain}","info");
		$this->output("query type:{$queryType}","info");
		
		$ip = $this->Driver->get(rtrim($domain, "."));
		$this->output("ip address:{$ip}","info");
		if (!$ip) {
			// ローカル環境で名前解決出来ない場合はそとに行く
			$this->output("call lookupExternal","debug");
			return $this->lookupExternal($domain, $buffer, $client_ip, $client_port);
		}

		$answer = $buffer[0].$buffer[1].chr(129).chr(128).$buffer[4].$buffer[5].$buffer[4].$buffer[5].chr(0).chr(0).chr(0).chr(0);
		$answer .= substr($buffer, 12);
		$answer .= chr(192).chr(12);
		$answer .= chr(0).chr(1).chr(0).chr(1).chr(0).chr(0).chr(0).chr(60).chr(0).chr(4);
		
		
		$_ip ="";
		foreach(explode(".",$ip) as $v)	$_ip.=chr($v);
		$answer .=$_ip;

		if (socket_sendto($this->socket,$answer, strlen($answer), 0,$client_ip, $client_port) === false) {
			throw new PdnsException("not found");
		}
		
	}
	
	/**
	 * 外部のDNSに問い合わせに行く
	 * @param string $domain
	 * @param string $buffer
	 * @param string $client_ip
	 * @param string $client_port
	 * @throws PdnsException
	 */
	private function lookupExternal($domain, $buffer, $client_ip, $client_port) {
		$socket = socket_create(AF_INET,SOCK_DGRAM, SOL_UDP);
		$externalDnsIp = $this->get('external_dns');
		if (!$externalDnsIp) {
			throw new PdnsException("no external dns server ip address");
		}
		
		// 別DNSの問い合わせ
		socket_connect($socket, $externalDnsIp, 53);
		socket_send($socket, $buffer, strLen($buffer), 0);
		$resultBuffer = socket_read($socket, 1024*8);
		socket_close($socket);
		
		// ipアドレスの取得
		$tmp = substr($resultBuffer, -4, 4);
		$length = strlen($tmp);
		$ip = null;
		for ($i = 0; $i < $length; $i++) {
			$char = ord($tmp[$i]);
			echo $char;
			$ip .= $char."."; 
		}
		$ip = rtrim($ip,".");
		
		// キャッシュする
		if (!empty($ip)) {
			$this->Driver->set(rtrim($domain,"."),$ip);
		}
		
		// クライアントにDNSの結果を渡す
		socket_sendto($this->socket,$resultBuffer, strlen($resultBuffer), 0,$client_ip, $client_port);
	}
	
	private function output($message,$prefix = "") {
		
		if (! $this->get('stdout')) {
			return;
		}
		
		$output = "";
		if (!empty($prefix)) {
			$output = "[{$prefix}]";
		}
		$output .= $message ."\n";
		echo $output;
	}
	
	private function getTypes() {
		return array(
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
	}
	
	
}