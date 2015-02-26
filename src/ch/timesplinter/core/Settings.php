<?php

namespace ch\timesplinter\core;

use ch\timesplinter\common\JsonUtils;

class Settings
{
	private $settings;
	private $settingsPath;
    private $replace;
	private $cacheFile;
	private $cacheChanged;
	private $cachedSettingsFiles;
	private $cachedFileTime;
	private $resourcesChecked;
	
	public function __construct($settingsPath, $cachePath, array $replace = array())
	{
		$this->settingsPath = $settingsPath;
		$this->settings = new \stdClass();
        $this->replace = array();
		$this->resourcesChecked = array();

		$this->cacheFile = $cachePath . 'settings.cache';
		$this->cacheChanged = false;
		$this->loadSettingsFromCache();

        foreach($replace as $k => $v) {
            $this->replace['${' . $k . '}'] = $v;
        }
	}

	private function loadSettingsFromCache()
	{
		if(file_exists($this->cacheFile) === false)
			return;

		$cacheFileContent = file_get_contents($this->cacheFile);

		if(strlen($cacheFileContent) === 0)
			return;

		$settingsCache = null;
		$settingsCacheFiles = null;

		$cachedData = unserialize($cacheFileContent);

		$this->settings = $cachedData['settings'];
		$this->cachedFileTime = $cachedData['cachetime'];

		$this->cachedSettingsFiles = $cachedData['files'];
	}

	private function loadSettingsFromFile($file)
	{
		$filePath = $this->settingsPath . $file;
		
		if(file_exists($filePath) === false)
			throw new SettingsException('Settings file does not exist: ' . $filePath);
		
		$content = file_get_contents($filePath);
		
		if(strlen($content) <= 0)
			return null;
		
		if($content === false)
			throw new SettingsException('Could not load settings file: ' . $filePath);

		try {
			$settingsObj = JsonUtils::decode($content, false, false);
		} catch(\Exception $e) {
			throw new SettingsException('Could not load settings file, JSON Error: ' . $e->getMessage() . ' in file: ' .$filePath );
		}

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
		
		//unset($settingsObj->{'@resources'});

		return $settingsObj;
	}

    private function interpolateObj(\stdClass $settingsObj, array $replace = array())
    {
        if(count($replace) === 0)
            return;

        foreach($settingsObj as $k => $v) {
            if(is_object($settingsObj->$k) && $settingsObj->$k instanceof \stdClass)
                $this->interpolateObj($settingsObj->$k, $replace);
            elseif(is_array($settingsObj->$k))
                $this->interpolateArray($settingsObj->$k, $replace);
            elseif(is_string($v) === true)
                $settingsObj->$k = strtr($v, $replace);
        }
    }

    private function interpolateArray(array $settingsArray, array $replace = array())
    {
        if(count($replace) === 0)
            return;

        foreach($settingsArray as $k => $v) {
            if(is_object($settingsArray[$k]) && $settingsArray[$k] instanceof \stdClass)
                $this->interpolateObj($settingsArray[$k], $replace);
            elseif(is_array($settingsArray[$k]))
                $this->interpolateArray($settingsArray[$k], $replace);
            else
                $settingsObj[$k] = strtr($v, $replace);
        }
    }

	public function __get($property)
	{
		/* property not exist OR cache file is older than the json file here */
		if(!isset($this->settings->$property) || filemtime($this->settingsPath . $property . '.json') > $this->cachedFileTime) {
			$this->settings->$property = $this->loadSettingsFromFile($property . '.json');
			$this->cacheChanged = true;
		}

		if(isset($this->settings->$property->{'@resources'}) === true && in_array($property, $this->resourcesChecked) === false) {
			$this->resourcesChecked[] = $property;

			foreach($this->settings->$property->{'@resources'} as $rsc) {
				if(filemtime($this->settingsPath . $rsc) > $this->cachedFiletime) {
					$this->settings->$property = $this->loadSettingsFromFile($property . '.json');
					$this->cacheChanged = true;

					break;
				}
			}
		}

		return $this->settings->$property;
	}

	public function __destruct()
	{
		if($this->cacheChanged === false)
			return;

		// TODO: save it to php
		file_put_contents($this->cacheFile, serialize(array(
			'cachetime' => time(),
			'files' => array(),
			'settings' => $this->settings
		)));
	}
}

/* EOF */