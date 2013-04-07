<?php
namespace Polidog\Pdns\Storage;
use Polidog\Pdns\Domain\DomainList;
use \Polidog\Pdns\Domain\Domain;
/**
 * ストレージ抽象化クラス
 * @property array $config 設定情報
 * @property DomainList $DomainList $name Description
 */
abstract class StorageAbstract {
	
	protected $config = array();
	protected $DomainList;
	
	public function __construct($config) {
		$this->config = $config;
		$this->DomainList = new DomainList();
	}
	
	/**
	 * データを取得する
	 * @param string $domain
	 * @param string $ip
	 */
	public function set($domain,$ip, $expir = -1) {
		$this->DomainList->set(new Domain($domain,$ip,$expir));
		return $this;
	}
		
	/**
	 * ドメイン情報を取得する
	 * @param string $domain
	 * @return Domain;
	 */
	public function get($domain) {
		return $this->DomainList->searchDomain($domain);
	}
	
	abstract function cache($domain,$ip,$expir);
}