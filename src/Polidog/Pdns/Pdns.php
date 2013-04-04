<?php
namespace Polidog\Pdns;
use Polidog\Pdns\Storage\Storage;
class Pdns {
	
	private $socket;
	
	private $settings = array(
		'localip'	=> '127.0.0.1',
		'port'		=> '53',
		'storage'	=> 'List',
		'storageConfig' => array(),
	);
	
	
	public function set($key, $value = null) {
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
	
	
	/**
	 * DNSサーバを起動する
	 */
	public function listen($port = null, $localip = null) {
		$this->loadStorage();
		
		if ($port == null) $port = $this->get('port');
		if ($localip == null) $localip = $this->get('localip');
		
		
		$this->socket = socket_create(AF_INET,SOCK_DGRAM, SOL_UDP);
		if ($this->socket < 0) {
			throw new PdnsException('socket no create');
		}
		if (socket_bind($this->socket, $localip, $port) == false) {
			throw new PdnsException('socket no bind');
		}
		
		while(true) {
			 $len = socket_recvfrom($this->socket, $buf, 1024*4, 0, $ip, $port);
			 if ($len > 0) {
				 $this->dns($buf,$ip,$port);
			 }
		}
	}
	
	private function loadStorage() {
		$storageType = $this->get('storege');
		if (!$storageType) {
			throw new PdnsException("not storage type");
		}
		$this->Storage = new Storage($storageType, $this->get('storegeConfig'));
	}
	

	private function domainToIp($domain) {
		$domains = array(
			'www.polidog.jp' => "133.242.145.155"
		);
		if (isset($domains[$domain]) ) {
			return $domains[$domain];
		}
		return false;
	}
	
	private function dns($buf,$clientip,$clientport) {
		$domain = '';
		$tmp = substr($buf,12);
		$length = strlen($tmp);
		
		for ($i = 0; $i < $length; $i++) {
			$len = ord($tmp[$i]);
			if ($len == 0) {
				break;
			}
			$domain .= substr($tmp,$i+1, $len).".";
			$i += $len;
		}
		
		echo "request domain:{$domain}\n";
		
		$ip = $this->domainToIp($domain);
		
		echo "get ip:{$ip}\n";
		if (!$ip ) {
			// @todo dns 転送
			$transSocket = socket_create(AF_INET,SOCK_DGRAM, SOL_UDP);
			socket_connect($transSocket, '8.8.8.8', 53);
			socket_send($transSocket, $buf, strLen($buf), 0);
			$outbuf = socket_read($transSocket,1024*8);
			
			$ret = socket_sendto($this->socket,$outbuf, strlen($outbuf), 0,$clientip, $clientport);
			return;
		}
		
		
        $i++;$i++;		
		$querytype = array_search((string)ord($tmp[$i]), $this->getTypes() ) ;
        $answ = $buf[0].$buf[1].chr(129).chr(128).$buf[4].$buf[5].$buf[4].$buf[5].chr(0).chr(0).chr(0).chr(0);
        $answ .= $tmp;
        $answ .= chr(192).chr(12);
        $answ .= chr(0).chr(1).chr(0).chr(1).chr(0).chr(0).chr(0).chr(60).chr(0).chr(4);
		$answ .= $this->transformIP($this->getIp());

		if (socket_sendto($this->socket,$answ, strlen($answ), 0,$clientip, $clientport) === false) {
			throw new PdnsException("not found");
		}	
	}
	
	private function getIp() {
		return "133.242.145.155";
	}
	
	
	private function transformIP($ip) {
        $_ip ="";
        foreach(explode(".",$ip) as $value) {
			$_ip.=chr($value);
		}
        return $_ip;		
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