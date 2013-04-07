<?php
namespace Polidog\Pdns\Storage;
class FileStorage extends StorageAbstract {
	
	private $DomainList;
	
	public function __construct($config) {
		parent::__construct($config);
		$this->DomainList = new DomainList();
	}
	
	
	public function get($domain) {
		;
	}
	
	public function set($domain, $ip, $expir = -1) {
		;
	}
	
	public function cache($domain, $ip, $expir = 3600) {
		$this->cache[] = new Domain($domain,$ip,$expir);
	}
	
}