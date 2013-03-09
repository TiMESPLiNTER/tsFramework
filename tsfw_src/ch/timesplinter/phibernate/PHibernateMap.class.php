<?php

/**
 * Description of PHibernateMap
 *
 * @author Pascal Muenst
 * @copyright Copyright (c) 2012, Metanet AG
 * @version 1.0
 */
class PHibernateMap {

	private $columns;
	private $property;
	private $class;
	private $onDelete;

	function __construct($property, $class) {
		$this->property = $property;
		$this->class = $class;
		$this->columns = array();
		
		$this->onDelete = PHibernate::ACTION_KEEP;
	}

	public function getColumns() {
		return $this->columns;
	}

	public function addColumn(PHibernateColumn $column) {
		$this->columns[] = $column;
	}

	public function getProperty() {
		return $this->property;
	}

	public function setProperty($property) {
		$this->property = $property;
	}

	public function getClass() {
		return $this->class;
	}

	public function setClass($class) {
		$this->class = $class;
	}

	public function getOnDelete() {
		return $this->onDelete;
	}

	public function setOnDelete($onDelete) {
		$this->onDelete = $onDelete;
	}

}

?>
