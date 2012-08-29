<?php

/**
 * TemplateCache
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class TemplateCache {
	/** @var Logger */
	private $logger;
	
	private $filePath;
	private $cachePath;
	private $registry;
	private $cacheChanged;
	private $saveOnDestruct;

	public function __construct($cachePath, $filePath) {
		$this->logger = LoggerFactory::getLoggerByName('dev', $this);
		
		$this->registry = array();
		$this->cachePath = $cachePath;
		$this->filePath = $filePath;

		$this->registry = $this->loadCacheFile();

		$this->cacheChanged = false;
		$this->saveOnDestruct = true;
	}
	
	private function loadCacheFile() {
		$cacheFilePath = $this->cachePath . $this->filePath;

		if(file_exists($cacheFilePath) === false) {
			return array();
		}

		$content = json_decode(file_get_contents($cacheFilePath), true);
		
		return $content;
	}

	private function saveCacheFile() {
		if($this->cacheChanged === false) {
			return;
		}

		$cacheFilePath = $this->cachePath . $this->filePath;

		$savedFile = file_put_contents($cacheFilePath, json_encode($this->registry));

		if($savedFile === false) {
			$this->logger = LoggerFactory::getEnvLogger($this);
			$this->logger->error('Could not write template cache-file: ' . $cacheFilePath);
			
			return;
		}
		
		$this->logger->debug('tplcache-file rewritten');
	}

	public function getCachedTplFile($tplFile) {
		if(array_key_exists($tplFile, $this->registry) === false)
			return null;

		return $this->registry[$tplFile];
	}

	/**
	 *
	 * @param string $tplFile
	 * @param string $id
	 * @param int $size
	 */
	public function addCachedTplFile($tplFile, $id, $size, $changeTime) {
		$this->registry[$tplFile] = array('filename' => $tplFile, 'id' => $id, 'size' => $size, 'changetime' => $changeTime);
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

?>