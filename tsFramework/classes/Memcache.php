<?php

/**
 * @author Pascal Münst
 * @copyright Copyright 2012 METANET AG
 * @version 1.0
 */
class Memcache {
	private $values;
	
	public function __construct() {
		$this->values = array();
	}
	
	/**
	 * Returns a stores value
	 * @param string $key
	 */
	public function getValue($key) {
		return $this->value[$key];
	}
}