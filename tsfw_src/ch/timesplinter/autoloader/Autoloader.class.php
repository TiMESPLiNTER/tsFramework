<?php

namespace ch\timesplinter\autoloader;

use ch\timesplinter\core\Observable;
use ch\timesplinter\logger\LoggerFactory;

/**
 * automatically loads requested classes if they exist in classes (sub-)directory
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version	1.0.0
 */
class Autoloader {
	const CACHING_FILE = 'cache.autoload';

	const MODE_UNDERSCORE = 'underscore';
	const MODE_NAMESPACE = 'namespace';

	//private $logger;
	private $cachedClasses;
	private $cachedClassesChanged;
	private $cacheFile;

	private $loadPaths;

	public function __construct() {
		$this->loadPaths = array();
		$this->cachedClasses = array();
		$this->cachedClassesChanged = false;
		$this->cacheFile = CACHE_DIR . self::CACHING_FILE;

		$this->loadCache();
	}

	/**
	 * Fills the cache array with the known classes
	 */
	private function loadCache() {
		$cacheFile = $this->cacheFile;

		if(file_exists($cacheFile) === false) {
			return;
		}
		
		$serialized = file_get_contents($this->cacheFile); 
		$this->cachedClasses = json_decode($serialized, true);
	}

	/**
	 * Registers the autoload function
	 */
	public function register() {
		spl_autoload_register(array($this, 'doAutoload'));
		//$this->logger = LoggerFactory::getLoggerByName('dev', $this);
	}

	/**
	 * Checks if a class is cached and returns the cached filepath. If not false is returned.
	 * @param $className The classname to check if it's cached
	 * @return bool|string The cached filepath or false
	 */
	private function isCached($className) {
		if(isset($this->cachedClasses[$className]) === false)
			return false;

		$classPath = FW_DIR . $this->cachedClasses[$className];

		if(file_exists($classPath) === true) {
			self::notifyObservers($className);
			return $classPath;
		} elseif(file_exists('phar://' . $classPath) === true) {
			self::notifyObservers($className);
			return 'phar://' . $classPath;
		}

		return false;
	}

	/**
	 * Autoloads a class from the cache file or the file system
	 * @param $class_name string Name of the class to be loaded
	 * @throws AutoloaderException
	 */
	private function doAutoload($class_name) {
		if(class_exists($class_name) === true)
			return;


		if(($includePath = $this->isCached($class_name)) !== false) {
			echo 'loaded from cache: ' , $class_name , "\n";

			require $includePath;
			return;
		}

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
			array_shift($classPathParts);

			$phpFilePath = implode(DIRECTORY_SEPARATOR, $classPathParts);

			foreach($pathOptions['class_suffix'] as $cs) {
				$includePath = $path . $phpFilePath . $cs;

				if(file_exists($includePath) === false)
					continue;

				$this->doInclude($includePath, $class_name);
				return;
			}

			//echo $classPath , '<br>';
			//throw new AutoloaderException('Could not load class: ' . $class_name);
		}

		$this->cachedClassesChanged = true;
	}

	private function doInclude($includePath, $className) {
		require $includePath;

		$this->cachedClasses[$className] = $includePath;
	}

	private function pharInclude($includePath, $classPath, $interfacePath) {
		if(file_exists(FW_DIR . $includePath) === false)
			return null;

		$libs = scandir(FW_DIR . $includePath);
		
		foreach($libs as $lib) {
			if(in_array($lib, array('.', '..')) === true)
				continue;
			
			$pharClass = $includePath . $lib . '/' . $classPath;
			if(file_exists('phar://' . FW_DIR . $pharClass) === true)
				return $pharClass;
			
			$pharInterface = $includePath . $lib . '/' . $interfacePath;
			if(file_exists('phar://' . FW_DIR . $pharInterface) === true)
				return $pharInterface;
		}
		
		return null;
	}

	public function addPath($id, array $pathOptions) {
		$this->loadPaths[$id] = $pathOptions;
	}

	/**
	 * Writes the new entries into the cache file (if there are any)
	 */
	public function __destruct() {
		if($this->cachedClassesChanged === false) {
			return;
		}

		$cacheFile = $this->cacheFile;
		
		$serialized = json_encode($this->cachedClasses); 
		file_put_contents($cacheFile, $serialized); 
	}
}

/* EOF */