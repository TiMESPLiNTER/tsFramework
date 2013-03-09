<?php

namespace ch\timesplinter\core;

use \stdClass;
use ch\timesplinter\common\JsonUtils;

class Settings {
	private $settings;
	private $settingsPath;
	
	public function __construct($settingsPath) {
		$this->settingsPath = $settingsPath;
		$this->settings = new stdClass;
	}
	
	private function loadSettingsFromFile($file) {
		$filePath = $this->settingsPath . $file;
		
		if(file_exists($filePath) === false)
			throw new SettingsException('Settings file does not exist: ' . $filePath);
		
		$content = file_get_contents($filePath);
		
		if(strlen($content) <= 0)
			return null;
		
		if($content === false)
			throw new SettingsException('Could not load settings file: ' . $filePath);
	
		$settingsObj = JsonUtils::decode($content, false, false);
	
		if($settingsObj === null)
			throw new SettingsException('Invalid JSON code in settings file: ' . $filePath);
	
		if(isset($settingsObj->{'@resources'}) === false)
			return $settingsObj;
		
		foreach($settingsObj->{'@resources'} as $res) {
			if(($loadedRes = self::loadSettingsFromFile($res)) === null)
				continue;
		
			foreach($loadedRes as $k => $v)
				$settingsObj->$k = isset($settingsObj->$k)?(object)array_merge((array)$settingsObj->$k, (array)$v):$v;
		}
		
		unset($settingsObj->{'@resources'});
	
		return $settingsObj;
	}
	
	public function __get($property) {
		if(!isset($this->settings->$property))
			$this->settings->$property = self::loadSettingsFromFile($property . '.json');
			
		return $this->settings->$property;
	}
}

/* EOF */