<?php

namespace ch\timesplinter\autoloader;

/**
 * Automatically loads requested classes if they exist in classes (sub-)directory
 * @package ch\timesplinter\autoloader
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 */
class Autoloader {
	const MODE_UNDERSCORE = 'underscore';
	const MODE_NAMESPACE = 'namespace';

	private $cachedClasses;
	private $cachedClassesChanged;
	private $cacheFile;

	private $loadPaths;
	private $usedPackages;

	public function __construct($cacheFilePath = null) {
		$this->loadPaths = array();
		$this->usedPackages = array();
		$this->cachedClasses = array();
		$this->cachedClassesChanged = false;
		$this->cacheFile = $cacheFilePath;

		$this->loadCache();
	}

	/**
	 * Fills the cache array with the known classes
	 */
	private function loadCache() {
		if($this->cacheFile === null || file_exists($this->cacheFile) === false)
			return;
		
		$serialized = file_get_contents($this->cacheFile); 
		$this->cachedClasses = json_decode($serialized, true);
	}

	/**
	 * Registers the autoload function
	 */
	public function register() {
		spl_autoload_register(array($this, 'doAutoload'));
	}

	/**
	 * Checks if a class is cached and returns the cached filepath. If not false is returned.
	 * @param $className The classname to check if it's cached
	 * @return bool|string The cached filepath or false
	 */
	private function isCached($className) {
		if(isset($this->cachedClasses[$className]) === false)
			return false;

		$classPath = $this->cachedClasses[$className];

		if(file_exists($classPath) === true)
			return $classPath;
		elseif(file_exists('phar://' . $classPath) === true)
			return 'phar://' . $classPath;

		return false;
	}

	/**
	 * Autoloads a class from the cache file or the file system
	 * @param $class_name string Name of the class to be loaded
	 * @throws AutoloaderException
	 * @throws \Exception
	 * @return bool
	 */
	private function doAutoload($class_name) {
		/*if(class_exists($class_name, false) === true)
			return;*/

		if(($includePath = $this->isCached($class_name)) !== false) {
			require $includePath;
			return true;
		}

		$searchedPaths = array();

		foreach($this->loadPaths as $id => $pathOptions) {
			$delimiter = null;

			$path = $pathOptions['path'];
			$mode = $pathOptions['mode'];

			if($mode === self::MODE_NAMESPACE) {
				$delimiter = '\\';
			} elseif($mode === self::MODE_UNDERSCORE) {
				$delimiter = '_';
			} else {
				throw new AutoloaderException('Unknown mode for path "' . $path . '": ' . $mode);
			}

			$classPathParts = explode($delimiter, $class_name);

			// Throw first dir / array element away
			//array_shift($classPathParts);

			$phpFilePath = implode(DIRECTORY_SEPARATOR, $classPathParts);

			foreach($pathOptions['class_suffix'] as $cs) {
				$includePath = $path . $phpFilePath . $cs;

				if(stream_resolve_include_path($includePath) === false) {
					$searchedPaths[] = $includePath;
					continue;
				}

				$this->doInclude($includePath, $class_name);

				return true;
			}
		}

		//echo $classPath , '<br>';
		//throw new AutoloaderException('Could not load class: ' . $class_name);
		//throw new \Exception('Could not find class ' . $class_name . '. Searched in: ' . implode(", \n", $searchedPaths));

		return false;
	}

	private function doInclude($includePath, $className) {
		require_once $includePath;

		$this->cachedClasses[$className] = $includePath;
		$this->cachedClassesChanged = true;
	}

	/*private function pharInclude($includePath, $classPath, $interfacePath) {
		if(file_exists($this->baseIncludePath . $includePath) === false)
			return null;

		$libs = scandir($this->baseIncludePath . $includePath);
		
		foreach($libs as $lib) {
			if(in_array($lib, array('.', '..')) === true)
				continue;
			
			$pharClass = $includePath . $lib . '/' . $classPath;
			if(file_exists('phar://' . $this->baseIncludePath . $pharClass) === true)
				return $pharClass;
			
			$pharInterface = $includePath . $lib . '/' . $interfacePath;
			if(file_exists('phar://' . $this->baseIncludePath . $pharInterface) === true)
				return $pharInterface;
		}
		
		return null;
	}*/

	public function addPath($id, array $pathOptions) {
		$this->loadPaths[$id] = str_replace('/', DIRECTORY_SEPARATOR, $pathOptions);
	}

	/**
	 * Writes the new entries into the cache file (if there are any)
	 */
	public function __destruct() {
		if($this->cacheFile === null || $this->cachedClassesChanged === false)
			return;
		
		$serialized = json_encode($this->cachedClasses); 
		file_put_contents($this->cacheFile, $serialized);
	}
}

/* EOF */