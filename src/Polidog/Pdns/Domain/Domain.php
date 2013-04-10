<?php
namespace Polidog\Pdns\Domain;
use  \Polidog\Pdns\Exception\PdnsDomainException;
class Domain {

	private $domain;
	private $ipAddress;
	
	private $startExpirTime;
	private $endExpirTime;
	
	private $defaultExpir = 3600;
	
	/**
	 * コンストラクタ
	 * @param string $domain
	 * @param string $ipAddress
	 * @param int $expir timestamp
	 */
	public function __construct($domain = null, $ipAddress = null, $expir = -1) {
		$this->set($domain,$ipAddress,$expir);
	}
	
	/**
	 * マジックメソッド
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		if ($name == 'domain' || $name == 'ipAddress') {
			if (!empty($this->$name)) {
				return $this->$name;
			}
		}
	}
	
	/**
	 * 指定したドメインがあるかチェックする
	 * @param string $domainName
	 * @return boolean
	 */
	public function domainExist($domainName) {
		return ($this->domain == $domainName);
	}
	
	/**
	 * IPアドレスがあるかチェックする
	 * @param string $ipAddress
	 * @return boolean
	 */
	public function ipAddressExist($ipAddress) {
		return ($this->ipAddress == $ipAddress);
	}
	
	/**
	 * 有効なドメインかの判定を行う
	 * @return boolean
	 */
	public function is() {
		if ($this->ipAddress && $this->domain && $this->isExpired() == false) {
			return true;
		}
		return false;
	}	
	
	/**
	 * 有効期限を確認する
	 * @return boolean
	 */
	public function isExpired() {
		if (is_null($this->startExpirTime) && is_null($this->endExpirTime)) {
			return false;
		}
		return ( $this->endExpirTime < time() );
	}
	
	public function ttl() {
		if (empty($this->startExpirTime) && empty($this->endExpirTime)) {
			return -1;
		}		
		
		$time =  $this->endExpirTime - time();
		if ($time < 0) {
			$time = 0;
		}
		return $time;
	}
	
	
	/**
	 * バイナリとしてipアドレスを返す
	 * @return binnary
	 */
	public function exportIpAddressBinary() {
		
		if (!$this->is()) {
			throw new PdnsDomainException("not use domain object");
		}
		
		$_ipAddress = "";
		foreach (explode(".", $this->ipAddress) as $v) {
			$_ipAddress.=chr($v);
		}
		return $_ipAddress;
	}
	

	/**
	 * データをセットする
	 * @param string $domain
	 * @param string $ipAddress
	 * @param int $expir
	 */
	private function set($domain, $ipAddress, $expir = -1) {
		
		if (!empty($expir) && $expir !== -1) {
			$this->startExpirTime = time();
			$this->endExpirTime = $this->startExpirTime + $expir;
		}
		
		$this->domain = $domain;
		$this->ipAddress = $ipAddress;
	}	
	
}