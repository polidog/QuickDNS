<?php
namespace Polidog\QuickDns\Storage;
use \Polidog\QuickDns\Domain\DomainIterator;
use \Polidog\QuickDns\Exception\QuickDnsException;
use \Polidog\QuickDns\Domain\Domain;

/**
 * APCストレージ
 */
class ApcStorage implements StorageInterface {
	
	private $cacheKey = 'quickdns_cache';
	
	
	public function __construct($domainList = array()) {
		if (!is_array($domainList) || empty($domainList)) {
			return ;
		}
		
		if (! function_exists('apc_add')) {
			throw new QuickDnsException("not apc installed!!");
		}
		
		$info = @apc_cache_info('user');
		if (!$info) {
			throw new QuickDnsException("No APC info available.");
		}
		
		foreach ($domainList as $domain => $ip) {
			$this->save(new Domain($domain,$ip));
		}		
	}
	
	
	/**
	 * ドメインを検索する
	 * @param stirng $domainName
	 * @return \Polidog\QuickDns\Storage\Domain
	 */
	public function searchDomain($domainName) {
		$iterator = new DomainIterator($this->getDomainList());
		foreach ($iterator as $domain) {
			if ($domain->domainExist($domainName)) {
				return $domain;
			}
		}
		return new Domain();
	}
	
	
	/**
	 * IPを検索する
	 * @param type $ipAddress
	 * @return \Polidog\QuickDns\Storage\Domain
	 */
	public function searchIPAddress($ipAddress) {
		$iterator = new DomainIterator($this->getDomainList());
		foreach ($iterator as $domain) {
			if ($domain->ipAddressExist($ipAddress)) {
				return $domain;
			}
		}
		return new Domain();
	}
	
	
	/**
	 * ドメインを保存する
	 * @param \Polidog\QuickDns\Domain\Domain $domain
	 * @return \Polidog\QuickDns\Storage\ApcStorage
	 */
	public function save(\Polidog\QuickDns\Domain\Domain $domain) {
		$DomainList = $this->getDomainList();
		$DomainList->append($domain);
		$this->setDomainList($DomainList);
		return $this;
	}
	
	
	/**
	 * ドメインリストの取得
	 * @return \ArrayObject
	 */
	private function getDomainList() {
		$DomainList = apc_fetch($this->cacheKey);
		if (! $DomainList) {
			$DomainList = new \ArrayObject();
			apc_add($this->cacheKey, $DomainList);
		}
		return $DomainList;
	}
	
	
	/**
	 * ドメインリストの保存
	 * @param ArrayObject $domainList
	 */
	private function setDomainList($domainList) {
		if (apc_exists($this->cacheKey)) {
			apc_store($this->cacheKey, $domainList);
		} else {
			apc_add($this->cacheKey, $domainList);
		}
		return $this;
	}
}