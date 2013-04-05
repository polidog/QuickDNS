<?php
namespace Polidog\Pdns;

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
	 */
	public function listen($port = null, $localip = null) {
		
		if (!self::$isInit) {
			throw new PdnsException("dns no setup");
		}
		
		
		if ($port == null) $port = $this->get('port');
		if ($localip == null) $localip = $this->get('localip');
		
		
		$this->socket = socket_create(AF_INET,SOCK_DGRAM, SOL_UDP);
		if ($this->socket < 0) {
			throw new PdnsException('socket no create');
		}
		if (socket_bind($this->socket, $localip, $port) == false) throw new PdnsException('socket no bind');
		
		
		while(true) {
			 $len = socket_recvfrom($this->socket, $buffer, 1024*4, 0, $ip, $port);
			 if ($len > 0) {
				 $this->lookup($buffer, $ip, $port);
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
		if ($queryType != "A") {
			// Aレコード以外はムリポ
			return $this->lookupExternal($buffer, $client_ip, $client_port);
		}

		$ip = $this->Driver->get(rtrim($domain, "."));
		if (!$ip) {
			// ローカル環境で名前解決出来ない場合はそとに行く
			return $this->lookupExternal($buffer, $client_ip, $client_port);
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
	
	private function lookupExternal($buffer, $client_ip, $client_port) {
		$socket = socket_create(AF_INET,SOCK_DGRAM, SOL_UDP);
		$externalDnsIp = $this->get('external_dns');
		if (!$externalDnsIp) {
			throw new PdnsException("no external dns server ip address");
		}
		
		socket_connect($socket, $externalDnsIp, 53);
		socket_send($socket, $buffer, strLen($buffer), 0);
		$resultBuffer = socket_read($socket, 1024*8);
		socket_close($socket);
		
		socket_sendto($this->socket,$resultBuffer, strlen($resultBuffer), 0,$client_ip, $client_port);
	}
	
	
//	private function dns($buf,$clientip,$clientport) {
//		$domain = '';
//		$tmp = substr($buf,12);
//		$length = strlen($tmp);
//		
//		for ($i = 0; $i < $length; $i++) {
//			$len = ord($tmp[$i]);
//			if ($len == 0) {
//				break;
//			}
//			$domain .= substr($tmp,$i+1, $len).".";
//			$i += $len;
//		}
//		
//		echo "request domain:{$domain}\n";
//		
//		$ip = $this->domainToIp($domain);
//		
//		echo "get ip:{$ip}\n";
//		if (!$ip ) {
//			// @todo dns 転送
//			$transSocket = socket_create(AF_INET,SOCK_DGRAM, SOL_UDP);
//			socket_connect($transSocket, '8.8.8.8', 53);
//			socket_send($transSocket, $buf, strLen($buf), 0);
//			$outbuf = socket_read($transSocket,1024*8);
//			
//			$ret = socket_sendto($this->socket,$outbuf, strlen($outbuf), 0,$clientip, $clientport);
//			return;
//		}
//		
//		
//        $i++;$i++;		
//		$querytype = array_search((string)ord($tmp[$i]), $this->getTypes() ) ;
//        $answ = $buf[0].$buf[1].chr(129).chr(128).$buf[4].$buf[5].$buf[4].$buf[5].chr(0).chr(0).chr(0).chr(0);
//        $answ .= $tmp;
//        $answ .= chr(192).chr(12);
//        $answ .= chr(0).chr(1).chr(0).chr(1).chr(0).chr(0).chr(0).chr(60).chr(0).chr(4);
//		$answ .= $this->transformIP($this->getIp());
//
//		if (socket_sendto($this->socket,$answ, strlen($answ), 0,$clientip, $clientport) === false) {
//			throw new PdnsException("not found");
//		}	
//	}
//	
//	private function getIp() {
//		return "133.242.145.155";
//	}
//	
//	
//	private function transformIP($ip) {
//        $_ip ="";
//        foreach(explode(".",$ip) as $value) {
//			$_ip.=chr($value);
//		}
//        return $_ip;		
//	}
//	
//	
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