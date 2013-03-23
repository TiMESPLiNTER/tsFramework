<?php

error_reporting(E_ALL);

use ch\timesplinter\core\Core;
use ch\timesplinter\core\ErrorHandler;
use ch\timesplinter\autoloader\Autoloader;

// Framework specific constants
define('REQUEST_TIME', $_SERVER['REQUEST_TIME']+microtime());
define('FW_DIR', dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR);
define('MODULES_DIR', 'modules' . DIRECTORY_SEPARATOR);

// Site specific constants
define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR, true);
define('CACHE_DIR' , SITE_ROOT . 'cache' . DIRECTORY_SEPARATOR);
define('SETTINGS_DIR' , SITE_ROOT . 'settings' . DIRECTORY_SEPARATOR);

// Initalize ErrorHandler
/*require FW_DIR . 'ch/timesplinter/core/FrameworkException.class.php';
require FW_DIR . 'ch/timesplinter/core/PHPException.class.php';
require FW_DIR . 'ch/timesplinter/core/ErrorHandler.class.php';
$errorHandler = new ErrorHandler();
$errorHandler->register();*/

// Initialize Autoloader
require FW_DIR . 'ch/timesplinter/core/Observable.class.php';
require FW_DIR . 'ch/timesplinter/autoloader/Autoloader.class.php';
require FW_DIR . 'ch/timesplinter/autoloader/AutoloaderException.class.php';
$autoloader = new Autoloader();
$autoloader->addPath('fw-logic', array(
	'path' => FW_DIR . 'ch/', Autoloader::MODE_NAMESPACE,
	'mode' => 'namespace',
	'class_suffix' => array('.class.php', '.interface.php')
));

$autoloader->register();

$core = new Core();

$core->sendResponse();

/* EOF */
