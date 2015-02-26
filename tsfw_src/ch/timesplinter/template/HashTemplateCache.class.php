<?php

namespace ch\timesplinter\template;

/**
 * TemplateCache
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.0
 */
class HashTemplateCache extends TemplateCacheStrategy
{
	const CACHE_SUFFIX = '.cache';
	
	private $filePath;
	private $registry;
	private $cacheChanged;
    private $logger;

	public function __construct($cachePath, $filePath)
	{
		parent::__construct($cachePath);
		
		$this->registry = array();
		$this->filePath = $filePath;
		
		$this->registry = $this->loadCacheFile();

		$this->cacheChanged = false;
	}

	private function loadCacheFile()
	{
		$cache = array();
		$cacheFilePath = $this->cachePath . $this->filePath;

		if(file_exists($cacheFilePath) === false) {
			return $cache;
		}

		$content = json_decode(file_get_contents($cacheFilePath));
		
		$entries = array();
		
		foreach($content as $key => $entry) {
			$tplCacheEntry = new TemplateCacheEntry();
			
			$tplCacheEntry->changeTime = $entry->changeTime;
			$tplCacheEntry->path = $entry->path;
			$tplCacheEntry->size = $entry->size;
			
			$entries[$key] = $tplCacheEntry;
		}
		
		return $entries;
	}

	protected function saveCacheFile()
	{
		if($this->cacheChanged === false) {
			return;
		}

		$cacheFilePath = $this->cachePath . $this->filePath;

		$fp = file_put_contents($cacheFilePath, json_encode($this->registry));

		if($fp === false) {
			$this->logger->error('Could not write template cache-file: ' . $cacheFilePath);
		}
	}
	
	

	/**
	 * 
	 * @param string $tplFile
	 * @return TemplateCacheEntry
	 */
	public function getCachedTplFile($tplFile)
	{
		if($this->registry === null || array_key_exists($tplFile, $this->registry) === false)
			return null;

		return $this->registry[$tplFile];
	}

	/**
	 * @param string $tplFile
	 * @param TemplateCacheEntry|null $currentCacheEntry
	 * @param string $compiledTemplateContent
	 *
	 * @throws TemplateEngineException
	 * @return TemplateCacheEntry
	 */
	public function addCachedTplFile($tplFile, $currentCacheEntry, $compiledTemplateContent)
	{
		// NEW HERE
		$cacheFileName = ($currentCacheEntry !== null)?$currentCacheEntry->path:uniqid() . self::CACHE_SUFFIX;
		$cacheFilePath = $this->cachePath . $cacheFileName;
		
		if(stream_resolve_include_path($cacheFilePath) === true && is_writable($cacheFilePath) === false)
			throw new TemplateEngineException('Cache file is not writable: ' . $cacheFilePath);

		$fp = @fopen($cacheFilePath, 'w');

		if($fp !== false) {
			fwrite($fp, $compiledTemplateContent);
			fclose($fp);

			$this->saveOnDestruct = true;
		} else {
			throw new TemplateEngineException('Could not cache template-file: ' . $cacheFilePath);
		}

		$fileSize = @filesize($tplFile);

		if(($changeTime = @filemtime($tplFile)) === false)
			$changeTime = @filectime($tplFile);
		
		$tplCacheEntry = new TemplateCacheEntry();
		
		$tplCacheEntry->path = $cacheFileName;
		$tplCacheEntry->size = $fileSize;
		$tplCacheEntry->changeTime = $changeTime;
				
		$this->registry[$tplFile] = $tplCacheEntry; //new TemplateCacheEntry($tplFile, $id, $size, $changeTime);
		$this->cacheChanged = true;

		return $tplCacheEntry;
	}

	public function __destruct()
	{
		if($this->saveOnDestruct === false)
			return;

		$this->saveCacheFile($this->registry);
	}
}

/* EOF */