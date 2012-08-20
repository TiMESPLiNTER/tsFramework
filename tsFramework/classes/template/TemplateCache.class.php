<?php

/**
 * TemplateCache
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class TemplateCache {

	private $filePath;
	private $cachePath;
	private $registry;
	private $cacheChanged;
	private $saveOnDestruct;

	public function __construct($cachePath, $filePath) {

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

		$content = file($cacheFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		foreach($content as $l) {
			if($l === '')
				continue;

			$lParts = explode('=', $l);

			if(!array_key_exists(3, $lParts))
				$lParts[3] = -1;

			$cache[$lParts[0]] = new TemplateCacheEntry($lParts[0], $lParts[2], (int) $lParts[1], (int) $lParts[3]);
		}

		return $cache;
	}

	private function saveCacheFile() {
		if($this->cacheChanged === false) {
			return;
		}

		$cacheFilePath = $this->cachePath . $this->filePath;

		$fp = fopen($cacheFilePath, 'w');

		if($fp !== false) {
			foreach($this->registry as $entry) {
				$str = (string) $entry . "\n";
				fwrite($fp, $str);
			}

			fclose($fp);
		} else {
			$this->logger = LoggerFactory::getEnvLogger($this);
			$this->logger->error('Could not write template cache-file: ' . $cacheFilePath);
		}
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
		$this->registry[$tplFile] = new TemplateCacheEntry($tplFile, $id, $size, $changeTime);
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