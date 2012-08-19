<?php

/**
 * Description of Settings
 *
 * @author pascal91
 */
class Settings {
	private $values;
	
	public function __construct() {
		$this->values = array();
	}
	
	public function setValue($key, $value) {
		if(strpos($key,'tsfw_') === 0 && array_key_exists($key, $this->values) === true)
			throw new Exception('You can not overwrite system values!');
		
		$this->values[$key] = $value;
	}
	
	public function getValue($key) {
		if(array_key_exists($key, $this->values) === false)
			return null;
		
		return $this->values[$key];
	}
}

?>