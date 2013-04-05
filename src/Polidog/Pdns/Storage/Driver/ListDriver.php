<?php
namespace Polidog\Pdns\Storage\Drivar;
class ListDriver extends DrivarAbstract {
	
	private $list = array();
	
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
