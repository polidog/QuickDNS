<?php
namespace Polidog\QuickDns\Storage;
use Polidog\QuickDns\Domain\Domain;
/**
 * 
 */
interface StorageInterface {
	
	public function searchDomain($domainName);
	
	public function searchIPAddress($ipAddress);
	
	public function save(Domain $domain);
}