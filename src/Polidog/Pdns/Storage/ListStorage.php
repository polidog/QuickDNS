<?php
namespace Polidog\Pdns\Storage;
use Polidog\Pdns\Exception\PdnsException;
use Polidog\Pdns\Domain\DomainIterator;
use Polidog\Pdns\Domain\Domain;

/**
 * リスト型のストレージ
 * @property ArrayObject $DomainList 
 */
class ListStorage implements StorageInterface {
	
	public function __construct($config = array()) {
		
		$this->DomainList = new \ArrayObject();
		
		
		
		if (!isset($config['data']) || empty($config['data'])) {
			throw PdnsException("domain name list not found!!");
		}
		
		
		foreach ($config['data'] as $domain => $ip) {
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
		var_dump(count($iterator));
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
	 * @param \Polidog\Pdns\Storage\Domain $domain
	 * @return \Polidog\Pdns\Storage\ListStorage
	 */
	public function save(Domain $domain) {
		if ($domain->is()) {
			$this->DomainList->append($domain);
		}
		return $this;
	}
}
