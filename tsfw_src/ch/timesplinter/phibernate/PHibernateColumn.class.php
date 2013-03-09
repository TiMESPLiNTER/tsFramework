<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PHibernateColumn
 *
 * @author Pascal Münst (Actra AG)
 * @copyright
 * @version
 */
class PHibernateColumn {
	private $name;
	private $nullable;
	private $references;
	private $type;
	
	function __construct($name, $nullable, $references, $type) {
		$this->name = $name;
		$this->nullable = $nullable;
		$this->references = $references;
		$this->type = $type;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function isNullable() {
		return $this->nullable;
	}

	public function setNullable($nullable) {
		$this->nullable = $nullable;
	}

	public function getReferences() {
		return $this->references;
	}

	public function setReferences($references) {
		$this->references = $references;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}
}

?>
