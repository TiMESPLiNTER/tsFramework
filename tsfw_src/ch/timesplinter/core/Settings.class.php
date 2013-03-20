<?php

namespace ch\timesplinter\core;

use \stdClass;
use ch\timesplinter\common\JsonUtils;

class Settings {
	private $settings;
	private $settingsPath;
    private $replace;
	
	public function __construct($settingsPath, array $replace = array()) {
		$this->settingsPath = $settingsPath;
		$this->settings = new stdClass;
        $this->replace = array();

        foreach($replace as $k => $v) {
            $this->replace['${' . $k . '}'] = $v;
        }
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

        // Replace some fw placeholders
        $this->interpolateObj($settingsObj, $this->replace);

		if(isset($settingsObj->{'@resources'}) === false)
			return $settingsObj;

		foreach($settingsObj->{'@resources'} as $res) {
			if(($loadedRes = $this->loadSettingsFromFile($res)) === null)
				continue;

			foreach($loadedRes as $k => $v)
				$settingsObj->$k = isset($settingsObj->$k)?(object)array_merge((array)$settingsObj->$k, (array)$v):$v;
		}
		
		unset($settingsObj->{'@resources'});

		return $settingsObj;
	}

    private function interpolateObj(stdClass $settingsObj, array $replace = array()) {
        if(count($replace) === 0)
            return;

        foreach($settingsObj as $k => $v) {
            if(is_object($settingsObj->$k) && $settingsObj->$k instanceof stdClass)
                $this->interpolateObj($settingsObj->$k, $replace);
            elseif(is_array($settingsObj->$k))
                $this->interpolateArray($settingsObj->$k, $replace);
            elseif(is_string($v) === true)
                $settingsObj->$k = strtr($v, $replace);
        }
    }

    private function interpolateArray(array $settingsArray, array $replace = array()) {
        if(count($replace) === 0)
            return;

        foreach($settingsArray as $k => $v) {
            if(is_object($settingsArray[$k]) && $settingsArray[$k] instanceof stdClass)
                $this->interpolateObj($settingsArray[$k], $replace);
            elseif(is_array($settingsArray[$k]))
                $this->interpolateArray($settingsArray[$k], $replace);
            else
                $settingsObj[$k] = strtr($v, $replace);
        }
    }

	public function __get($property) {
		if(!isset($this->settings->$property))
			$this->settings->$property = self::loadSettingsFromFile($property . '.json');
			
		return $this->settings->$property;
	}
}

/* EOF */