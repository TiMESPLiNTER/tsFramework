<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PHibernateForeignkey
 *
 * @author Pascal Münst (Actra AG)
 * @copyright
 * @version
 */
class PHibernateForeignkey {

	private $columns;
	private $name;
	private $class;
	private $joinIds;
	private $property;
	
	private $onDelete;

	function __construct($name, $class, $property) {
		$this->name = $name;
		$this->class = $class;
		$this->property = $property;

		$this->onDelete = PHibernate::ACTION_KEEP;
		
		$this->joinIds = array();
	}

	public function getColumns() {
		return $this->columns;
	}

	public function addColumn(PHibernateColumn $column) {
		$this->columns[] = $column;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getClass() {
		return $this->class;
	}

	public function setClass($class) {
		$this->class = $class;
	}

	public function getJoinId($pos) {
		if(array_key_exists($pos, $this->joinIds) === false)
			return null;

		return $this->joinIds[$pos];
	}

	public function getJoinIds() {
		return $this->joinIds;
	}

	public function addJoinId($pos, $joinId) {
		$this->joinIds[$pos] = $joinId;
	}

	public function clearJoinIds() {
		$this->joinIds = array();
	}

	public function getProperty() {
		return $this->property;
	}

	public function setProperty($property) {
		$this->property = $property;
	}
	
	public function getOnDelete() {
		return $this->onDelete;
	}

	public function setOnDelete($onDelete) {
		$this->onDelete = $onDelete;
	}
}

?>
