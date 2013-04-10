<?php
namespace Polidog\Pdns\Storage;
/**
 * 
 */
interface StorageInterface {
	
	public function searchDomain($domainName);
	
	
	public function searchIPAddress($ipAddress);
	
	
	public function save(Domain $domain);
}