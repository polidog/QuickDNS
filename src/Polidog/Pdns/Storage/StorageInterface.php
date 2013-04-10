<?php
namespace Polidog\Pdns\Storage;
use Polidog\Pdns\Domain\Domain;
/**
 * 
 */
interface StorageInterface {
	
	public function searchDomain($domainName);
	
	public function searchIPAddress($ipAddress);
	
	public function save(Domain $domain);
}