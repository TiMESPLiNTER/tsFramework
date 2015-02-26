<?php

namespace ch\timesplinter\template;

/**
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 */
abstract class TemplateCacheStrategy 
{
	protected $saveOnDestruct;
	protected $cachePath;

	public function __construct($cachePath)
	{
		if(file_exists($cachePath) === false)
			mkdir($cachePath, 0777, true);

		$this->cachePath = $cachePath;
		
		$this->saveOnDestruct = true;
	}
	
	/**
	 * 
	 * @param string $tplFile
	 * @return TemplateCacheEntry|null
	 */
	public abstract function getCachedTplFile($tplFile);

	/**
	 * @param string $tplFile
	 * @param TemplateCacheEntry|null $currentCacheEntry
	 * @param string $compiledTemplateContent
	 *
	 * @return TemplateCacheEntry Path to the cached template
	 */
	public abstract function addCachedTplFile($tplFile, $currentCacheEntry, $compiledTemplateContent);

	public function getCachePath()
	{
		return $this->cachePath;
	}

	/**
	 *
	 * @param boolean $saveOnDestruct
	 */
	public function setSaveOnDestruct($saveOnDestruct)
	{
		$this->saveOnDestruct = $saveOnDestruct;
	}
}

/* EOF */