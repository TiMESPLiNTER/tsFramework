<?php

namespace ch\timesplinter\core;

use ch\timesplinter\autoloader\Autoloader;
use \stdClass;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, Pascal Muenst
 * @version 1.0.0
 */
class FrameworkAutoloader extends Autoloader {
	/**
	 * Register additional paths from a framework related settings file
	 * @param stdClass $autoLoaderSettings
	 */
	public function addPathsFromSettings(stdClass $autoLoaderSettings) {
		foreach($autoLoaderSettings as $k => $o)
			$this->addPath($k, (array)$o);
	}
}

/* EOF */