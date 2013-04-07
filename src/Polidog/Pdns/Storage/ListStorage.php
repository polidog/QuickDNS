<?php
namespace Polidog\Pdns\Storage;
use Polidog\Pdns\Domain\DomainList;
use \Polidog\Pdns\Domain\Domain;

class ListStorage extends StorageAbstract {
	
	protected $DomainList;
	
	
	public function __construct($config) {
		parent::__construct($config);
		$this->DomainList = new DomainList();
		
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
	
	public function cache($domain, $ip, $expir = 3600) {
		$this->set($domain,$ip,$expir);
	}
	
}
