<?php

/**
 * @author Pascal Muenst
 * @copyright Copyright 2012 METANET AG
 * @version 1.0
 */
class TurboCacheFactory {
	const TYPE_AUTO = 0;
	const TYPE_MEMCACHED = 1;
	const TYPE_MEMCACHE = 2;
	const TYPE_FILECACHE = 3;
	
	private function __construct() {
	}
	
	public static function getInstance($cacheType = self::TYPE_AUTO, $selfInclude = false) {
		$instance = null;
		
		if($cacheType === self::TYPE_AUTO)
			$cacheType = self::autoDetect();
		
		switch($cacheType) {
			case self::TYPE_MEMCACHED:
				if($selfInclude === true)
					require siteRoot . fwDir . 'classes/caching/TurboMemcached.class.php';
				
				$instance = new TurboMemcached();
				break;
			case self::TYPE_MEMCACHE:
				if($selfInclude === true)
					require siteRoot . fwDir . 'classes/caching/TurboMemcache.class.php';
				
				$instance = new TurboMemcache();
				break;
			default:
				if($selfInclude === true)
					require siteRoot . fwDir . 'classes/caching/TurboFileCache.class.php';

				$instance = new TurboFileCache();
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