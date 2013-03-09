<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PHibernateJoin
 *
 * @author Pascal Münst (Actra AG)
 * @copyright
 * @version
 */
class PHibernateJoin {
	private $fk;
	private $joins;
	
	private $t1;
	private $t2;
	private $onColumns;
	
	public function __construct($fk, $t1,$t2) {
		$this->fk = $fk;
		
		$this->t1 = $t1;
		$this->t2 = $t2;
		$this->onColumns = array();
		$this->joins = array();
	}
	
	public function getT1() {
		return $this->t1;
	}

	public function setT1($t1) {
		$this->t1 = $t1;
	}

	public function getT2() {
		return $this->t2;
	}

	public function setT2($t2) {
		$this->t2 = $t2;
	}
	
	public function getJoins() {
		return $this->joins;
	}

	public function setJoins($joins) {
		$this->joins = $joins;
	}
	
	public function getJoinId() {
		return $this->joinId;
	}
	
	public function getOnColumns() {
		return $this->onColumns;
	}

	public function addOnColumn($col1, $col2) {
		$this->onColumns[$col1] = $col2;
	}
	
	public function __toString() {
		$colsArr = array();
		foreach($this->onColumns as $key => $var)
			$colsArr[] .= $this->t1 . '.' . $key . ' = ' . $this->joinId . '.' . $var;
		
		$sql = 'LEFT JOIN ' . $this->t2 . ' ' . $this->joinId . ' ON ' . implode(' AND ' , $colsArr) . "\n";
		
		foreach($this->joins as $join) {
			$colsSubArr = array();
			
			foreach($join->getOnColumns() as $key => $var)
				$colsSubArr[] = $this->joinId . '.' . $key . ' = ' . $join->getJoinId() . '.' . $var;
			
			$sql .= 'LEFT JOIN ' . $join->getT2() . ' ' . $join->getJoinId() . ' ON ' . implode(' AND ' , $colsSubArr);
		}
		
		return $sql;
	}
        
        public function getFk() {
            return $this->fk;
        }

        public function setFk($fk) {
            $this->fk = $fk;
        }
}

?>
