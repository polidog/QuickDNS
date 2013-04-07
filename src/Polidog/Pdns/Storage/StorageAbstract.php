<?php
namespace Polidog\Pdns\Storage;

/**
 * ストレージ抽象化クラス
 * @property array $config 設定情報
 * @property DomainList $DomainList $name Description
 */
abstract class StorageAbstract {
	
	protected $config = array();
	
	public function __construct($config) {
		$this->config = $config;
	}
	
	/**
	 * データを取得する
	 * @param string $domain
	 * @param string $ip
	 */
	abstract public function set($domain,$ip, $expir = -1);
		
	/**
	 * ドメイン情報を取得する
	 * @param string $domain
	 * @return Domain;
	 */
	abstract public function get($domain);
	
	
	/**
	 * キャッシュを生成する
	 */
	abstract function cache($domain,$ip,$expir);
}