<?php

/**
 * Condition for the criteria object of the PHibernate framework
 * (@see PHibernateCriteria)
 *
 * @author Pascal Muenst
 * @copyright Copyright (c) 2012, Metanet AG
 * @version 1.0
 */
class PHibernateCondition {
	private $column;
	private $operator;
	private $values;
	private $cond;
	
	/**
	 * 
	 * @param string $column The column to compare against the values
	 * @param string $operator The operator to compare
	 * @param mixed $values The values for the condition
	 */
	public function __construct($column, $operator, $values, $cond = PHibernate::COND_AND) {
		$this->column = $column;
		$this->operator = $operator;
		$this->values = $values;
		$this->cond = $cond;
	}
	
	public function getColumn() {
		return $this->column;
	}

	public function setColumn($column) {
		$this->column = $column;
	}

	public function getOperator() {
		return $this->operator;
	}

	public function setOperator($operator) {
		$this->operator = $operator;
	}

	public function getValues() {
		return $this->values;
	}

	public function setValues($values) {
		$this->values = $values;
	}
	
	public function getCond() {
		return $this->cond;
	}

	public function setCond($cond) {
		$this->cond = $cond;
	}
}

?>
