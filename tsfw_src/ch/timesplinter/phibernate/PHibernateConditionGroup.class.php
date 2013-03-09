<?php

/**
 * Description of PHibernateConditionGroup
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Metanet AG
 * @version 1.0
 */
class PHibernateConditionGroup {
	private $conditions;
	private $joinCondition;
	
	public function __construct($conditions, $joinCondition = PHibernate::COND_AND) {
		$this->conditions = $conditions;
		$this->joinCondition = $joinCondition;
	}
	
	public function getConditions() {
		return $this->conditions;
	}

	public function setConditions($conditions) {
		$this->conditions = $conditions;
	}

	public function getJoinCondition() {
		return $this->joinCondition;
	}

	public function setJoinCondition($joinCondition) {
		$this->joinCondition = $joinCondition;
	}
}

?>
