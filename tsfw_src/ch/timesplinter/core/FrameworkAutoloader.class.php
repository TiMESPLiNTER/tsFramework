<?php
/**
 * @author pascal91
 * @copyright Copyright (c) 2013, Pascal Muenst
 * @version 1.0.0
 */

namespace ch\timesplinter\core;


use ch\timesplinter\autoloader\Autoloader;
use ch\timesplinter\core\Settings;
use \stdClass;

class FrameworkAutoloader extends Autoloader {
	public function addPathsFromSettings(stdClass $autoloaderSettings) {
		foreach($autoloaderSettings as $k => $o) {
			$this->addPath($k, (array)$o);
		}
	}
}

/* EOF */