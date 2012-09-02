<?php

/**
 * automatically loads requested classes if they exist in classes (sub-)directory
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, METANET AG
 * @version	1.0
 */
class Autoloader extends Observable {
	const CACHING_FILE = 'cache.autoload';
	const SOURCE_DIR = 'modules/';

	private $cachedClasses;
	private $cachedClassesChanged;
	private $cacheFile;

	public function __construct() {
		$this->cachedClasses = array();
		$this->cachedClassesChanged = false;
		$this->cacheFile = SITE_ROOT . FW_DIR . 'cache/' . self::CACHING_FILE;

		$this->loadCache();
	}

	/**
	 * Fills the cache array with the known classes
	 * @return
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
	}

	/**
	 * Autoloads a class from the cache file or the file system
	 * @param	String $class_name
	 * @return
	 */
	private function doAutoload($class_name) {
		if(class_exists($class_name)) {
			return;
		}

		if(isset($this->cachedClasses[$class_name]) === true && file_exists($this->cachedClasses[$class_name]) === true) {
			require SITE_ROOT . FW_DIR . $this->cachedClasses[$class_name];
			return;
		}

		$classPath = $this->doClassSearch(self::SOURCE_DIR, $class_name);

		if($classPath === null)
			throw new AutoloaderException('Could not find class "' . $class_name . '"');
		
		require SITE_ROOT . FW_DIR . $classPath;
		self::setChanged();
		self::notifyObservers(array('path' => $classPath, 'class' => $class_name));
		
		$this->cachedClasses[$class_name] = $classPath;
		$this->cachedClassesChanged = true;
	}

	/**
	 * Recursive search in the directory to find the class
	 * @param	String $dir
	 * @param String $class_name
	 * @return String
	 */
	private function doClassSearch($dir, $class_name) {
		$files = scandir(SITE_ROOT . FW_DIR . $dir);

		foreach($files AS $f) {
			if(in_array($f, array('.', '..'))) {
				continue;
			}
			if($f === $class_name . '.class.php' || $f === $class_name . '.interface.php') {
				return $dir . $f;
			} elseif(is_dir(SITE_ROOT . FW_DIR . $dir . $f)) {
				$res = $this->doClassSearch($dir . $f . '/', $class_name);
				if($res !== null) {
					return $res;
				}
			}
		}
		return null;
	}

	/**
	 * Writes the new entries into the cache file (if there are any)
	 * @return
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

?>