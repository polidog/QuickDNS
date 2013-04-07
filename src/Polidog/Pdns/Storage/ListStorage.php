<?php
namespace Polidog\Pdns\Storage;
class ListStorage extends StorageAbstract {
	
	public function __construct($config) {
		parent::__construct($config);
		if (isset($config['data']) && is_array($config['data'])) {
			foreach ($config['data'] as $domain => $ip) {
				$this->set($domain, $ip);
			}
		}
	}
	
	public function cache($domain, $ip, $expir = 3600) {
		$this->set($domain,$ip,$expir);
	}
	
}
