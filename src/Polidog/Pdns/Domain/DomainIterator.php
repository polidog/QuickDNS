<?php
namespace Polidog\Pdns\Domain;

class DomainIterator extends \FilterIterator {
	
	public function __construct(array $domainList) {
		$arrayObject = new \ArrayObject();
		foreach ($domainList as $value) {
			$arrayObject->append($value);
		}
		parent::__construct($arrayObject->getIterator());
		$this->rewind();
	}
	
	public function count() {
		return $this->getInnerIterator()->count();
	}
	
	public function accept() {
		return $this->getInnerIterator()->current()->isExpir();
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