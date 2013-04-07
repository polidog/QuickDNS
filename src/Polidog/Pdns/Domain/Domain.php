<?php
namespace Polidog\Pdns\Domain;
class Domain {

	private $domain;
	private $ipAddress;
	
	private $startExpirTime;
	private $endExpirTime;
	
	private $defaultExpir = 3600;
	
	public function __construct($domain = null, $ipAddress = null, $expir = null) {
		$this->set($domain,$ipAddress,$expir);
	}
	
	public function __get($name) {
		if ($name == 'domain' || $name == 'ipAddress') {
			if (!empty($this->$name)) {
				return $this->$name;
			}
		} else if ($name == 'expir'){
			return $this->isExpir();
		}
		return false;
	}
	

	public function set($domain, $ipAddress, $expir = null) {
		
		if (!empty($expir) && $expir !== -1) {
			$this->startExpirTime = time();
			$this->endExpirTime = $this->startExpirTime + $expir;
		}
		
		$this->domain = $domain;
		$this->ipAddress = $ipAddress;
	}
	
	public function is() {
		if ($this->ipAddress && $this->domain && $this->isExpir()) {
			return true;
		}
		return false;
	}

	/**
	 * 有効期限を確認する
	 * @return boolean
	 */
	public function isExpir() {
		if (empty($this->startExpirTime) && empty($this->endExpirTime)) {
			return true;
		}
		return ( $this->endExpirTime > time() );
	}
	
	public function ttl() {
		if (empty($this->startExpirTime) && empty($this->endExpirTime)) {
			return -1;
		}		
		
		$time =  $this->endExpirTime - $this->startExpirTime;
		if ($time < 0) {
			$time = 0;
		}
		return $time;
	}
	
	public function exportIpAddressBinary() {
		$_ipAddress = "";
		foreach (explode(".", $this->ipAddress) as $v) {
			$_ipAddress.=chr($v);
		}
		return $_ipAddress;
	}

}