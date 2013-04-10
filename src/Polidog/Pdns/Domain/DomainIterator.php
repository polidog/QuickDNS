<?php
namespace Polidog\Pdns\Domain;

class DomainIterator extends \FilterIterator {
	
	public function __construct(\Iterator $iterator) {
		parent::__construct($iterator);
		$this->rewind();
	}
	
	public function accept() {
		return ($this->getInnerIterator()->current()->isExpired() == false);
	}
	
	public function currentIp() {
		$this->current()->ipAddress;
	}
	
	public function currentDomain() {
		return $this->current()->domain;
	}

	public function __toString() {
		return $this->current();
	}

}