<?php

/**
 * Description of FileCache
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Metanet AG
 * @version 1.0
 */
class TurboFileCache extends TurboCache {
	
	private $values;
	private $typeTable;
	private $cacheFile;
	
	public function init() {
		$this->values = array();
		$this->typeTable = array();
		$this->cacheFile = siteRoot . fwDir . 'cache/cache.json';
		
		// load persistent cache from disk (JSON)
		if(file_exists($this->cacheFile) === true) {
			$serialized = file_get_contents($this->cacheFile); 
			$this->values = json_decode($serialized, true);
			
			if(array_key_exists(':typeTable:', $this->values))
				$this->typeTable = $this->values[':typeTable:'];
		}
	}
	
	public function close() {
		if($this->cacheChanged === false)
			return;
		
		$this->values[':typeTable:'] = $this->typeTable;
		
		// write cache to disk (JSON) 
		$serialized = json_encode($this->values); 
		file_put_contents($this->cacheFile, $serialized); 
	}
	
	
	public function getValue($key) {
		$value =  $this->values[$key];
		
		if(array_key_exists($key, $this->typeTable) && $this->typeTable[$key] === true) {
			$value = unserialize($value);
		}
		
		return $value;
	}
	
	public function storeValue($key, $value, $gzip = false) {
		if(array_key_exists($key, $this->values) === true && $this->values[$key] === $value)
			return;
		
		if(is_object($value) === true) {
			$this->typeTable[$key] = true;
			$value = serialize($value);	
		}
		
		$this->values[$key] = $value;
		$this->cacheChanged = true;
	}
}

?>
