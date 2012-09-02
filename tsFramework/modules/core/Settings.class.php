<?php

/**
 * Description of Settings
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright (c) 2012, TiMESPLiNTER
 * @version 1.0
 */
class Settings implements Observer {
	private $collections;
	
	public function __construct() {
		$this->collections = array(
			'_general' => array()
		);
	}
	
	public function setValue($key, $value) {
		$this->collections['_general'][$key] = $value;
	}
	
	public function getValue($key, $collection = '_general') {
		if(isset($this->collections[$collection][$key]) === false)
			return null;
		
		return $this->collections[$collection][$key];
	}
	
	public function getAllValues($collection = '_general') {
		return $this->collections[$collection];
	}
	
	public function getAllCollections() {
		return $this->collections;
	}

	public function update(Observable $observable, $arg) {
		if($observable instanceof Autoloader) {
			$modName = StringUtils::beforeFirst(StringUtils::afterFirst($arg['path'], 'modules/'),'/');
			
			$settingsFile = SITE_ROOT . FW_DIR . 'settings/' . $modName . '.xml';
			if(isset($this->collections[$modName]) === true)
				return;
			
			//echo '<span style="color: #c00;">omg! settings file found for module ' .$modName . '</span><br>';
				
			$this->collections[$modName] = (file_exists($settingsFile) === true)?self::loadSettingsFile($settingsFile):array();
			//echo '<pre>'; var_dump($this->collections); echo'</pre>';
		}
	}
	
	private static function loadSettingsFile($filePath) {
		$settingsArr = array();
		
		$xml = simplexml_load_file($filePath);
		
		foreach($xml->setting as $setting) {
			$settingAttrs = $setting->attributes();
			
			$type = (string)$settingAttrs['type'];
			$name = (string)$settingAttrs['name'];
			$group = isset($settingAttrs['group'])?(string)$settingAttrs['group']:null;
			
			if(in_array($type, array('string', 'boolean', 'int', 'float')) === true) {
				$value = (string)$settingAttrs['value'];
				
				$settingsToSet = self::castString($value, $type);
			} elseif($type === 'array') {
				$settingsToSet = array();
				
				foreach($setting->property as $prop) {
					$propAttrs = $prop->attributes();
					
					$propName = (string)$propAttrs['name'];
					$propValue = (string)$propAttrs['value'];
					$propType = (string)$propAttrs['type'];
					
					$settingsToSet[$propName] = self::castString($propValue, $propType);
				}
			} else {
				$settingsToSet = new $type;
				
				foreach($setting->property as $prop) {
					$propAttrs = $prop->attributes();
					
					$propName = (string)$propAttrs['name'];
					$propValue = (string)$propAttrs['value'];
					$propType = (string)$propAttrs['type'];
					
					if(property_exists($settingsToSet, $propName) === false)
						continue;
				
					$valueNew = self::castString($propValue, $propType);
					$settingsToSet->$propName = $valueNew;
				}
			}
			
			if($group === null)
				$settingsArr[$name] = $settingsToSet;
			else
				$settingsArr[$group][$name] = $settingsToSet;
		}
		
		return $settingsArr;
	}
	
	private static function castString($value, $type) {
		if($type === 'int')
			return intval($value);
		elseif($type === 'boolean')
			return ($value === 'true')?true:false;
		
		return $value;
	}
}

?>