<?php

/**
 * Description of PHibernatePopo
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Actra AG
 * @version 1.0
 */
abstract class PHibernatePopo {
	const ACTION_NONE = 0;
	const ACTION_INSERT = 1;
	const ACTION_UPDATE = 2;
	const ACTION_DELETE = 3;
	
	private $objectChanged;
	private $null;
	private $actionOnSave;
	
	private $properties;
	
	public function __construct() {
		$this->objectChanged = null;
		$this->null = true;
		$this->actionOnSave = self::ACTION_INSERT;
		
		$this->properties = array();
		
		$reflection = new ReflectionClass($this);
		
		$defValues = $reflection->getDefaultProperties();
		
		foreach($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
			$propName = $prop->getName();
			
			$this->properties[$propName] = (array_key_exists($propName, $defValues) === true)?$defValues[$propName]:null;//$prop->getValue($prop);
			unset($this->$propName);
		}
	}
	
	public function __get($property) {
		self::checkProperty($property);
		
		return $this->properties[$property];
	}
	
	public function __set($property,$value) {
		self::checkProperty($property);
		
		if($this->properties[$property] !== $value) {
			$this->objectChanged = time();
		
			if($this->actionOnSave === PHibernatePopo::ACTION_NONE)
				$this->actionOnSave = PHibernatePopo::ACTION_UPDATE;
		}
		
		if($value !== null) {
			
			if($value instanceof PHibernateResultset) {
				if($value->count() > 0) {
					$this->null = false;
				}
			} elseif($value instanceof PHibernatePopo && $value->isNull() === true) {
			} else {
				$this->null = false;
			}
		}
		
		$this->properties[$property] = $value;
	}
	
	public function resetChanged() {
		$this->objectChanged = null;
		$this->actionOnSave = PHibernatePopo::ACTION_NONE;
	}
	
	public function setChanged($changed) {
		$this->objectChanged = $changed;
	}
	
	public function hasChanged() {
		if($this->objectChanged === null)
			return false;
		
		return true;
	}
	
	private function checkProperty($property) {
		if(in_array($property, array_keys($this->properties)) === false)
			throw new OutOfBoundsException('Property "' . $property . '" does not exists in class "' . get_class($this) . '"');
	}
	
	public function isNull() {
		return $this->null;
	}
	
	public function getActionOnSave() {
		return $this->actionOnSave;
	}

	public function setActionOnSave($actionOnSave) {
		$this->actionOnSave = $actionOnSave;
	}
	
	/**
	 * Delete this Popo on the next save 
	 */
	public function delete() {
		$this->objectChanged = true;
		$this->actionOnSave = self::ACTION_DELETE;
	}
}

?>
