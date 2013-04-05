<?php
namespace Polidog\Pdns\Driver;
abstract class DriverAbstract {
	
	private $config = array();
	
	public function __construct($config) {
		$this->config = $config;
	}
	
	abstract public function set($domain,$ip);
		
	abstract public function get($domain);
}