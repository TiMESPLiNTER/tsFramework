<?php

namespace ch\timesplinter\template;

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2014, METANET AG
 */
class DirectoryTemplateCache extends TemplateCacheStrategy
{
	const CACHE_SUFFIX = '.php';

	protected $baseDir;
	protected $baseDirLength;
	
	function __construct($cachePath, $baseDir = '/')
	{
		parent::__construct($cachePath);
		
		$this->baseDir = $baseDir;
		$this->baseDirLength = strlen($baseDir);
	}
	
	/**
	 *
	 * @param string $tplFile
	 *
	 * @return TemplateCacheEntry|null
	 */
	public function getCachedTplFile($tplFile)
	{
		$offset = (strpos($tplFile, $this->baseDir) !== false) ? $this->baseDirLength : 0;
		
		$cacheFileName = preg_replace('/\.\w+$/', self::CACHE_SUFFIX, substr($tplFile, $offset));
		$cacheFilePath = $this->cachePath . $cacheFileName;
		
		if(file_exists($cacheFilePath) === false)
			return null;
		
		if(($changeTime = filemtime($cacheFilePath)) === false)
			$changeTime = filectime($cacheFilePath);
		
		return $this->createTemplateCacheEntry($cacheFileName, $changeTime, -1);
	}

	/**
	 * @param string $tplFile
	 * @param TemplateCacheEntry|null $currentCacheEntry
	 * @param string $compiledTemplateContent
	 *
	 * @return TemplateCacheEntry Path to the cached template
	 */
	public function addCachedTplFile($tplFile, $currentCacheEntry, $compiledTemplateContent)
	{
		$offset = (strpos($tplFile, $this->baseDir) !== false) ? $this->baseDirLength : 0;

		$cacheFileName = preg_replace('/\.\w+$/', self::CACHE_SUFFIX, substr($tplFile, $offset));
		$cacheFilePath = $this->cachePath . $cacheFileName;

		if(file_exists($cacheFilePath) === true) {
			file_put_contents($cacheFilePath, $compiledTemplateContent);
			
			return $this->createTemplateCacheEntry($cacheFileName, time(), -1);
		}
		
		$fileLocation = pathinfo($cacheFilePath, PATHINFO_DIRNAME);

		if(is_dir($fileLocation) === false)
			mkdir($fileLocation, 0777, true);

		file_put_contents($cacheFilePath, $compiledTemplateContent);
		
		return $this->createTemplateCacheEntry($cacheFileName, time(), -1);
	}
	
	protected function createTemplateCacheEntry($path, $changeTime, $size)
	{
		$templateCacheEntry = new TemplateCacheEntry();

		$templateCacheEntry->path = $path;
		$templateCacheEntry->changeTime = $changeTime;
		$templateCacheEntry->size = $size;

		return $templateCacheEntry;
	}
}

/* EOF */