<?php
namespace ch\timesplinter\caching;

/**
 * @author Pascal Muenst
 * @copyright Copyright 2012 METANET AG
 * @version 1.0
 */
class CacheFactory {
	const TYPE_AUTO = 0;
	const TYPE_MEMCACHED = 1;
	const TYPE_MEMCACHE = 2;
	const TYPE_FILECACHE = 3;
	const TYPE_PHPCACHE = 3;
	
	private function __construct() {
	}
	
	public static function getInstance($cacheType = self::TYPE_AUTO, $selfInclude = false) {
		$instance = null;
		
		if($cacheType === self::TYPE_AUTO)
			$cacheType = self::autoDetect();
		
		switch($cacheType) {
			case self::TYPE_MEMCACHED:
				if($selfInclude === true)
					require SFW_DIR . 'classes/caching/MemcachedCache.class.php';
				
				$instance = new MemcachedCache();
				break;
			case self::TYPE_MEMCACHE:
				if($selfInclude === true)
					require FW_DIR . 'classes/caching/MemcacheCache.class.php';
				
				$instance = new MemcacheCache();
				break;
			/*case self::TYPE_PHPCACHE:
				if($selfInclude === true)
					require FW_DIR . 'classes/caching/PHPCache.class.php';
				
				$instance = new PHPCache();
				break;*/
			default:
				if($selfInclude === true)
					require FW_DIR . 'classes/caching/FileCache.class.php';

				$instance = new FileCache();
				break;
		}
		
		return $instance;
	}
	
	private static function autoDetect() {
		$cacheType = null;
		
		if(class_exists('memcached', false) === true) {
			$cacheType = self::TYPE_MEMCACHED;
		} elseif(class_exists('memcache', false) === true) {
			$cacheType = self::TYPE_MEMCACHE;
		} else {
			$cacheType = self::TYPE_FILECACHE;
		}
			
		return $cacheType;
	}
}