<?php

use ch\timesplinter\core\Core;
use ch\timesplinter\autoloader\Autoloader;
use ch\timesplinter\core\FrameworkAutoloader;

error_reporting(E_ALL);

// Default timezone patch
$defaultTimezone = 'Greenwich';
$iniTimezone = ini_get('date.timezone');

if(is_string($iniTimezone) === true && strlen($iniTimezone) > 0)
	$defaultTimezone = $iniTimezone;

date_default_timezone_set($defaultTimezone);

// Framework specific constants
define('REQUEST_TIME', $_SERVER['REQUEST_TIME']+microtime());

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