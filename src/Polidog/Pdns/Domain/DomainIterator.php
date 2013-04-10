<?php
namespace Polidog\Pdns\Domain;

class DomainIterator extends \ArrayIterator {
	
	public function __construct($value) {
		parent::__construct($value);
	}
	
	public function current() {
		$domain = parent::current();
		if (! $domain->is()) {
			$this->offsetUnset($this->key());
			return new Domain();
		}
		return $domain;
	}

	public function __toString() {
		return $this->current();
	}

}