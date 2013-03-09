<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PHibernateProperty
 *
 * @author Pascal Münst (Actra AG)
 * @copyright
 * @version
 */
class PHibernateProperty {
	const TYPE_UNKNOWN = 0;
	const TYPE_INT = 1;
	const TYPE_FLOAT = 2;
	const TYPE_DOUBLE = 2;
	const TYPE_STRING = 3;
	const TYPE_DATETIME = 4;
	
	const FK_RELATION_1_1 = 0;
	const FK_RELATION_1_N = 1;
	
	private $name;
	private $column;
	private $type;
	private $saveable;
	
	private $nullable;
	private $primaryKey;
	private $foreignKey;
	private $foreignKeyRelation;
	
	/**
	 *
	 * @param string $propertyName
	 * @param string $columnName
	 * @param int $type
	 */
	function __construct($name, $column, $type) {
		$this->name = $name;
		$this->column = $column;
		$this->type = $type;
		$this->savable = true;
		
		$this->nullable = false;
		$this->primaryKey = false;
		$this->foreignKey = null;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}

	public function getColumn() {
		return $this->column;
	}

	public function setColumn($column) {
		$this->column = $column;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getNullable() {
		return $this->nullable;
	}

	public function setNullable($nullable) {
		$this->nullable = $nullable;
	}

	public function getPrimaryKey() {
		return $this->primaryKey;
	}

	public function setPrimaryKey($primaryKey) {
		$this->primaryKey = $primaryKey;
	}

	public function getForeignKey() {
		return $this->foreignKey;
	}

	public function setForeignKey($foreignKey) {
		$this->foreignKey = $foreignKey;
	}
	
	public function getForeignKeyRelation() {
		return $this->foreignKeyRelation;
	}

	public function setForeignKeyRelation($foreignKeyRelation) {
		$this->foreignKeyRelation = $foreignKeyRelation;
	}
	
	public function isSaveable() {
		return $this->saveable;
	}

	public function setSaveable($saveable) {
		$this->saveable = $saveable;
	}
}

?>
