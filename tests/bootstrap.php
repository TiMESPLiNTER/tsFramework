<?php

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
$fwDir = dirname(__FILE__) . '/../tsfw_src/';

require_once $fwDir . 'ch/timesplinter/autoloader/Autoloader.class.php';
require_once $fwDir . 'ch/timesplinter/core/FrameworkAutoloader.class.php';

// Initialize Autoloader
$autoloader = new \ch\timesplinter\core\FrameworkAutoloader();
$autoloader->addPath('fw-logic', array(
	'path' => $fwDir,
	'mode' => \ch\timesplinter\autoloader\Autoloader::MODE_NAMESPACE,
	'class_suffix' => array('.class.php', '.interface.php')
));
$autoloader->register();

/* EOF */