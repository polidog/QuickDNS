<?php

namespace Polidog\Pdns\Domain;

class DomainList {

	private $data = array();

	public function set(Domain $domain) {
		$this->data[] = $domain;
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

	public function getIterator() {
		return new DomainIterator($this->data);
	}

}