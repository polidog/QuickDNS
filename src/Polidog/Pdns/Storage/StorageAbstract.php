<?php
namespace Polidog\Pdns\Storage;

/**
 * ストレージ抽象化クラス
 * @property array $config 設定情報
 * @property DomainList $DomainList $name Description
 */
abstract class StorageAbstract {
	
	protected $config = array();
	protected $cleaningStartTime;
	protected $cleaningSpanTime = 20;


	public function __construct($config) {
		$this->config = $config;
		$this->cleaningStartTime = time();
	}
	
	
	public function cleaningDomain() {
		$time = $this->cleaningStartTime + $this->cleaningSpanTime;
		$now = time();
		var_dump($time - $now);
		if ($now > $time) {
			echo "cleinigs tart";
			$this->clearExpiredCacheDomain();
			$this->cleaningStartTime = $now;
		}
		
		var_dump($this->getCacheList(true));
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
	 * 有効期限切れのキャッシュの削除
	 */
	abstract protected function clearExpiredDomain();
	
	/**
	 * キャッシュを生成する
	 */
	abstract public function setCache($domain, $ip, $expir);
	
	/**
	 * キャッシュを取得する
	 */
	abstract public function getCache($domain);
	
	/**
	 * キャッシュを破棄する
	 */
	abstract public function clearCache($domain);
	
	/**
	 * 有効期限切れのキャッシュを削除
	 */
	abstract protected function clearExpiredCacheDomain();
	
	abstract public function getList($isAll = false);
	abstract public function getCacheList($isAll = false);
}