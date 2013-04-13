<?php
namespace Polidog\QuickDns\Storage;
use Polidog\QuickDns\Exception\QuickDnsException;
use Polidog\QuickDns\Domain\DomainIterator;
use Polidog\QuickDns\Domain\Domain;

/**
 * ファイル型ストレージ
 */
class FileStorage implements StorageInterface
{
	private $DomainList;
	
	public function __construct(array $files,$type = 'ini') {
		if (empty($files)) {
			throw new QuickDnsException("file not found");
		}
		$this->DomainList = new \ArrayObject();
		$this->loadFile($files);
	}
	
	/**
	 * ドメインをサーチする
	 * @param string $domainName
	 * @return \Polidog\QuickDns\Domain\Domain
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
	 * @return \Polidog\QuickDns\Domain\Domain
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
	 * @param \Polidog\QuickDns\Domain\Domain $domain
	 * @return \Polidog\QuickDns\Storage\FileStorage
	 */
	public function save(Domain $domain) {
		if ($domain->is()) {
			$this->DomainList->append($domain);
		}
		return $this;
	}
	
	
	private function loadFile(array $paths) {
		foreach ($paths as $path) {
			if (file_exists($path)) {
				$tmp = parse_ini_file($path);
				if (is_array($tmp)) {
					foreach ($tmp as $domain => $ip) {
						$this->save(new Domain($domain,$ip));
					}
				}
			}
		}
	}
}