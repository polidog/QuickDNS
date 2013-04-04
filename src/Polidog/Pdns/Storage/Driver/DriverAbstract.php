<?php
namespace Polidog\Pdns\Storage\Drivar;
abstract class DrivarAbstract {
	
	private $domainList;
	
	abstract public function set($domain,$ip);
		
	abstract public function get($domain);
}