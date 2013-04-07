<?php
namespace Polidog\Pdns\Storage;
class FileStorage extends StorageAbstract {
	
	private $cache = array();
	
	
	public function cache($domain, $ip, $expir = 3600) {
		$this->cache[] = new Domain($domain,$ip,$expir);
	}
	
}