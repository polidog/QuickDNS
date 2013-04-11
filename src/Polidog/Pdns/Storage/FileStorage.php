<?php
namespace Polidog\Pdns\Storage;
use \Polidog\Pdns\Exception\PdnsException;
use \Polidog\Pdns\Domain\Domain;

class FileStorage implements StorageInterface
{
	private $DomainList;
	
	public function __construct($filename,$callback = null) {
		if (!file_exists($filename)) {
			throw new PdnsException("file not found path=".$filename);
		}
		$domains = null;
		if (!empty($callback)) {
			$domains = $callback($filename);
		} else {
			$domains = parse_ini_file($filename);
		}
		
		if (empty($domains)) {
			throw new PdnsException("data not found");
		}		
		
		foreach ($domains as $domain => $ip) {
			$this->save(new Domain($domain,$ip));
		}
	}
	
	/**
	 * ドメインをサーチする
	 * @param string $domainName
	 * @return \Polidog\Pdns\Domain\Domain
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
	 * IPを検索する
	 * @param string $ipAddress
	 * @return \Polidog\Pdns\Domain\Domain
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
	 * ドメインを記録する
	 * @param \Polidog\Pdns\Domain\Domain $domain
	 * @return \Polidog\Pdns\Storage\FileStorage
	 */
	public function save(Domain $domain) {
		if ($domain->is()) {
			$this->DomainList->append($domain);
		}
		return $this;
	}
}