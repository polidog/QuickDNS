<?php
namespace Polidog\QuickDns\Storage;
use Polidog\QuickDns\Exception\QuickDnsException;
use Polidog\QuickDns\Domain\DomainIterator;
use Polidog\QuickDns\Domain\Domain;

/**
 * リスト型のストレージ
 * @property ArrayObject $DomainList 
 */
class ListStorage implements StorageInterface {
	
	public function __construct(array $domainList) {
		
		$this->DomainList = new \ArrayObject();
		
		if (empty($domainList)) {
			throw QuickDnsException("domain name list not found!!");
		}
		
		foreach ($domainList as $domain => $ip) {
			$this->save(new Domain($domain,$ip));
		}
	}
	
	/**
	 * ドメイン検索を行う
	 * @param type $domainName
	 * @return Domain
	 */
	public function searchDomain($domainName) {
		$iterator = new DomainIterator($this->DomainList);
		foreach ($iterator as $domain) {
			if ($domain->domainExist($domainName)) {
				return $domain;
			}
		}
		return new Domain();
	}
	
	/**
	 * IPアドレスから検索する
	 * @param string $ipAddress
	 * @return Domain
	 */
	public function searchIPAddress($ipAddress) {
		$iterator = new DomainIterator($this->DomainList);
		foreach ($iterator as $domain) {
			if ($domain->current()->ipAddressExist($ipAddress)) {
				return $domain->current();
			}
		}
		return new Domain();
	}
	
	/**
	 * ドメインの保存処理
	 * @param \Polidog\QuickDns\Storage\Domain $domain
	 * @return \Polidog\QuickDns\Storage\ListStorage
	 */
	public function save(Domain $domain) {
		if ($domain->is()) {
			$this->DomainList->append($domain);
		}
		return $this;
	}
}
