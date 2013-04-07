<?php

namespace Polidog\Pdns\Domain;

class DomainList {

	private $ArrayObject;
	
	public function __construct($data = array()) {
		if (!empty($data)) {
			$this->ArrayObject = new \ArrayObject($data);
		} else {
			$this->ArrayObject = new \ArrayObject();
		}
	}
	
	/**
	 * セットする
	 * @param Domain $domain
	 */
	public function set(Domain $domain) {
		$this->ArrayObject->append($domain);
		return $this;
	}

	/**
	 * ドメインを検索する
	 * @param string $domain
	 * @return \Polidog\Pdns\Storage\Domain
	 */
	public function searchDomain($domainName) {
		return $this->_search($domainName, 'domain');
	}

	/**
	 * ip検索を行う
	 * @param string $ip
	 * @return \Polidog\Pdns\Storage\Domain
	 */
	public function serachIp($ip) {
		return $this->_search($ip, 'ipAddress');
	}
	
	/**
	 * ドメインの削除を行う
	 * @param type $domain
	 * @return boolean
	 */
	public function clearDomain($domain) {
		$domainIterator = $this->getIterator();
		$return = false;
		while($domainIterator->valid()) {
			if ($domainIterator->current()->domain == $domain) {
				if ( $this->ArrayObject->offsetExists($domainIterator->key()) ) {
					$this->ArrayObject->offsetUnset($domainIterator->key());
					$return = true;
					break;
				}
			}
			$domainIterator->next();
		}
		return $return;
	}
	
	/**
	 * 検索処理
	 * @param string $target
	 * @param stirng $type ip or domain
	 * @return \Polidog\Pdns\Storage\Domain
	 */
	private function _search($target,$type) {
		$type = strtolower($type);
		if (is_array($target)) {
			$return = array();
			foreach ($target as $t) {
				$return[] = $this->_search($t, $type);
			}
			return $return;
		} else {
			
			$domainIterator = $this->getIterator();
			while ($domainIterator->valid()) {
				if ($domainIterator->current()->$type == $target) {
					return $domainIterator->current();
				}
				$domainIterator->next();
			}
			return new Domain();
		}
		
	}

	/**
	 * イテレータを取得する
	 * @return DomainIterator
	 */
	public function getIterator() {
		return new DomainIterator($this->ArrayObject->getIterator());
	}

}