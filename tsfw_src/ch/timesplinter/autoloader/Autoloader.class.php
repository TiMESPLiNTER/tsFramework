<?php
namespace ch\timesplinter\autoloader;

use ch\timesplinter\core as core;
use ch\timesplinter\logger\LoggerFactory;

/**
 * automatically loads requested classes if they exist in classes (sub-)directory
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, METANET AG
 * @version	1.0
 */
class Autoloader extends core\Observable {
	const CACHING_FILE = 'cache.autoload';
	const SOURCE_DIR = 'modules/';

	private $logger;
	private $cachedClasses;
	private $cachedClassesChanged;
	private $cacheFile;

	public function __construct() {
		$this->cachedClasses = array();
		$this->cachedClassesChanged = false;
		$this->cacheFile = CACHE_DIR . self::CACHING_FILE;

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
		$this->logger = LoggerFactory::getLoggerByName('dev', $this);
	}

	/**
	 * Autoloads a class from the cache file or the file system
	 * @param	String $class_name
	 * @return
	 */
	private function doAutoload($class_name) {
		if(class_exists($class_name) === true)
			return;
		
		if(isset($this->cachedClasses[$class_name]) === true) {
			$classPath = FW_DIR . $this->cachedClasses[$class_name];
			if(file_exists($classPath) === true) {
				self::notifyObservers($class_name);
				require $classPath; return;
			} elseif(file_exists('phar://' . $classPath) === true) {
				self::notifyObservers($class_name);
				require 'phar://' . $classPath; return;
			}
		}

		//$classPath = $this->doClassSearch(self::SOURCE_DIR, $class_name);
		$filePath = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, $class_name);
		$classPath = $filePath . '.class.php';
		$interfacePath = $filePath . '.interface.php';
		//$includePath = null;
		$phar = false;
		$internal = null;
		
		// Look in src directory
		//$this->logger->debug('load: ' . FW_DIR . $classPath); 
		if(file_exists(FW_DIR . $classPath) === true) {
			$includePath = FW_DIR . $classPath;
		} elseif(file_exists(FW_DIR . $interfacePath) === true) {
			$includePath = FW_DIR . $interfacePath;
		} else {
			$includePath = self::pharInclude(MODULES_DIR, $classPath, $interfacePath);
			if($includePath !== null)
				$phar = true;
		}
		
		// Look in site directory
		if($includePath === null) {
			$internal = 'site' . DIRECTORY_SEPARATOR;
			//var_dump(FW_DIR . $internal . $classPath); exit;
			if(file_exists(SITE_ROOT .  $classPath) === true) {
				$includePath = SITE_ROOT . $classPath;
			} elseif(file_exists(SITE_ROOT .  $interfacePath) === true) {
				$includePath = SITE_ROOT . $interfacePath;
			}
		}
		
		if($includePath === null) {
			$this->logger->error('Could not find class \'' . $class_name . '\' expected at \'' . dirname (FW_DIR . $classPath) . '\' or \'' . dirname(FW_DIR . $internal . $classPath) . '\'');
			throw new AutoloaderException('Could not find class \'' . $class_name . '\' expected at \'' . dirname (FW_DIR . $classPath) . '\' or \'' . dirname(FW_DIR . $internal . $classPath) . '\'');
		}
		
		require  (($phar === true)?'phar://':null) . $includePath;
		
		self::setChanged();
		self::notifyObservers($class_name);
		
		$this->cachedClasses[$class_name] = $includePath;
		$this->cachedClassesChanged = true;
	}

	private function pharInclude($includePath, $classPath, $interfacePath) {
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