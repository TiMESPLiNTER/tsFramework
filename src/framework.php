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

$core = new Core($fwRoot, $siteRoot);

$core->sendResponse();

/* EOF */