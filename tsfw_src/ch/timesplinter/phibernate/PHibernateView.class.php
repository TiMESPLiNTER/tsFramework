<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PHibernateView
 *
 * @author Pascal Münst (Actra AG)
 * @copyright
 * @version
 */
class PHibernateView {
	private $selector;
	private $identifier;
	private $property;
	private $type;
	
	function __construct($identifier, $selector, $property, $type) {
		$this->selector = $selector;
		$this->identifier = $identifier;
		$this->property = $property;
		$this->type = $type;
	}

	public function getSelector() {
		return $this->selector;
	}

	public function setSelector($selector) {
		$this->selector = $selector;
	}

	public function getIdentifier() {
		return $this->identifier;
	}

	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	public function getProperty() {
		return $this->property;
	}

	public function setProperty($property) {
		$this->property = $property;
	}
	
	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}
}

?>
