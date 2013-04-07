<?php
namespace Polidog\Pdns\Storage;
use Polidog\Pdns\Domain\DomainList;
use \Polidog\Pdns\Domain\Domain;

class ListStorage extends StorageAbstract {
	
	protected $DomainList;
	protected $CacheDomainList;




	public function __construct($config) {
		parent::__construct($config);
		$this->DomainList = new DomainList();
		$this->CacheDomainList = new DomainList();
		
		if (isset($config['data']) && is_array($config['data'])) {
			foreach ($config['data'] as $domain => $ip) {
				$this->set($domain, $ip);
			}
		}
		
	}
	
	public function get($domain) {
		return $this->DomainList->searchDomain($domain);
	}
	
	public function set($domain, $ip, $expir = -1) {
		$this->DomainList->set(new Domain($domain,$ip,$expir));
		return $this;
	}
	
	public function getCache($domain) {
		return $this->CacheDomainList->searchDomain($domain);
	}
	
	public function setCache($domain, $ip, $expir) {
		$this->CacheDomainList->set(new Domain($domain,$ip,$expir));
		return $this;
	}
	
	public function clearCache($domain) {
		return $this->CacheDomainList->clearDomain($domain);
	}


	
}
