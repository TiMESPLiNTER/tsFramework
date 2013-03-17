<?php
namespace ch\timesplinter\template;

use ch\timesplinter\logger\TSLogger;

/**
 * TemplateCache
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.0
 */
class TemplateCache {
	private $filePath;
	private $cachePath;
	private $registry;
	private $cacheChanged;
	private $saveOnDestruct;
    private $logger;

	public function __construct($cachePath, $filePath) {
		if(file_exists($cachePath) === false)
			mkdir ($cachePath, 0777, true);
		
		$this->registry = array();
		$this->cachePath = $cachePath;
		$this->filePath = $filePath;
		
		$this->registry = $this->loadCacheFile();

		$this->cacheChanged = false;
		$this->saveOnDestruct = true;
	}

	private function loadCacheFile() {
		$cache = array();
		$cacheFilePath = $this->cachePath . $this->filePath;

		if(file_exists($cacheFilePath) === false) {
			return $cache;
		}

		$content = json_decode(file_get_contents($cacheFilePath));
		
		$entries = array();
		foreach($content as $key => $entry) {
			$entries[$key] = $entry;
		}
		
		return $entries;
	}

	private function saveCacheFile() {
		if($this->cacheChanged === false) {
			return;
		}

		$cacheFilePath = $this->cachePath . $this->filePath;

		$fp = file_put_contents($cacheFilePath, json_encode($this->registry));

		if($fp === false) {
			$this->logger = TSLogger::getEnvLogger('dev',$this);
			$this->logger->error('Could not write template cache-file: ' . $cacheFilePath);
		}
	}

	/**
	 * 
	 * @param type $tplFile
	 * @return TemplateCacheEntry
	 */
	public function getCachedTplFile($tplFile) {
		if($this->registry === null || array_key_exists($tplFile, $this->registry) === false)
			return null;

		return $this->registry[$tplFile];
	}

	/**
	 * @param string $tplFile
	 * @param string $id
	 * @param int $size
     * @param int $changeTime
	 */
	public function addCachedTplFile($tplFile, $id, $size, $changeTime) {
		$tplCacheEntry = new TemplateCacheEntry;
		$tplCacheEntry->ID = $id;
		$tplCacheEntry->size = $size;
		$tplCacheEntry->changeTime = $changeTime;
		//$tplCacheEntry->tplFile = $tplFile;
		
		
		$this->registry[$tplFile] = $tplCacheEntry; //new TemplateCacheEntry($tplFile, $id, $size, $changeTime);
		$this->cacheChanged = true;
	}

	public function __destruct() {
		if($this->saveOnDestruct === false)
			return;

		self::saveCacheFile($this->registry);
	}

	public function getCachePath() {
		return $this->cachePath;
	}

	/**
	 *
	 * @param boolean $saveOnDestruct
	 */
	public function setSaveOnDestruct($saveOnDestruct) {
		$this->saveOnDestruct = $saveOnDestruct;
	}

}

/* EOF */