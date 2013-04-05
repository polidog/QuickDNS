<?php
namespace Polidog\Pdns\Driver;
class ListDriver extends DriverAbstract {
	
	private $list = array(
		'www.polidog.jp' => '133.242.145.155',
	);
	
	public function get($domain) {
		if (isset($this->list[$domain]) ) {
			return $this->list[$domain];
		}
		return false;
	}
	
	public function set($domain, $ip) {
		$this->list[$domain] = $ip;
		return $this;
	}
}

//ListDirver
//ListDriver