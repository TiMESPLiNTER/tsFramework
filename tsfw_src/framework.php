<?php

error_reporting(E_ALL);

use ch\timesplinter\core\Core;
use ch\timesplinter\autoloader\Autoloader;
use ch\timesplinter\core\FrameworkAutoloader;

// Framework specific constants
define('REQUEST_TIME', $_SERVER['REQUEST_TIME']+microtime());
/*define('FW_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('MODULES_DIR', 'modules' . DIRECTORY_SEPARATOR);*/

// Site specific constants
/*define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR, true);
define('CACHE_DIR' , SITE_ROOT . 'cache' . DIRECTORY_SEPARATOR);
define('SETTINGS_DIR' , SITE_ROOT . 'settings' . DIRECTORY_SEPARATOR);*/

$fwRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$siteRoot = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR;

// Initialize Autoloader
require_once $fwRoot . 'ch/timesplinter/autoloader/Autoloader.class.php';
require_once $fwRoot . 'ch/timesplinter/core/FrameworkAutoloader.class.php';

$autoloader = new FrameworkAutoloader($siteRoot . 'cache' . DIRECTORY_SEPARATOR . 'cache.autoload');
$autoloader->addPath('fw-logic', array(
	'path' => $fwRoot,
	'mode' => Autoloader::MODE_NAMESPACE,
	'class_suffix' => array('.class.php', '.interface.php')
));
$autoloader->addPath('site-logic', array(
	'path' => $siteRoot,
	'mode' => Autoloader::MODE_NAMESPACE,
	'class_suffix' => array('.class.php', '.interface.php')
));

$autoloader->register();

$core = new Core($fwRoot, $siteRoot);

$core->sendResponse();

/* EOF */