<?php

/**
 * Define criterias for the load() method of the PHibernate class
 * (@see PHibernate)
 *
 * @author Pascal Münst
 * @copyright Copyright 2012 (c), Metanet AG
 * @version 1.0
 */
class PHibernateCriteria {
	private $forMethodPath;
	private $orders;
	private $conditionGroups;
	
	private $resultRange;
	
	/**
	 * Register the condition on a method path
	 * @param string $forMethodPath The method path to register the condition for
	 */
	public function __construct($forMethodPath) {
		$this->forMethodPath = $forMethodPath;
		
		$this->orders = array();
		$this->conditionGroups = array();
		
		$this->resultRange = null;
	}
	
	/**
	 * Adds an order to the criterium
	 * @param string $column The column which you want to sort
	 * @param string $order The order of the column 
	 * (PHibernate::ORDER_ASC, PHibernate::ORDER_DESC)
	 */
	public function addOrder($column, $order) {
		$this->orders[$column] = $order;
	}
	
	/**
	 * Returns the specified result range for this criteria
	 * @return array The result range as array. On the first position the
	 * beginning and on the second the number of records
	 */
	public function getResultRange() {
		return $this->resultRange;
	}
	
	/**
	 * Sets a result range for this criteria
	 * @param int $start The starting position
	 * @param int $numberOfResults The amount of results you want to have
	 */
	public function setResultRange($start,$numberOfResults) {
		$this->resultRange = array($start,$numberOfResults);
	}
	
	/**
	 * Returns the method path for which this criteria is registered
	 * @return string The method path
	 */
	public function getMethodPath() {
		return $this->forMethodPath;
	}
	
	/**
	 * Adds a condition group to this criteria
	 * @param PHibernateConditionGroup $groupArray An bunch of conditions (@see PHibernateCondition)
	 * @param string $condition The condition to join the group to another group
	 */
	public function addConditionGroup(PHibernateConditionGroup $conditionGroup) {
		$this->conditionGroups[] = $conditionGroup;
	}
	
	/**
	 * Returns all registered condition groups
	 * @return array The condition groups for this criteria
	 */
	public function getConditionGroups() {
		return $this->conditionGroups;
	}
	
	/**
	 * Returns all order defintions
	 * @return array The order definitions for this criteria
	 */
	public function getOrders() {
		return $this->orders;
	}
}

?>
