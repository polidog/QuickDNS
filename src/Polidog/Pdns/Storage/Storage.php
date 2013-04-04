<?php
namespace Polidog\Pdns\Storage;

/**
 * ストレージクラス
 */
class Storage {
	private $Dirver;
	
	public function __construct($dirverType,$dirverConfig) {
		$this->Dirver = new $dirverType($dirverConfig);
	}
	
	public function get($domain) {
		return $this->Dirver->get($domain);
	}
	
	public function set($domain,$ip) {
		$this->Dirver->set($domain,$ip);
		return $this;
	}
	
}