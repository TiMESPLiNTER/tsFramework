<?php

error_reporting(E_ALL);

define('REQUEST_TIME', $_SERVER['REQUEST_TIME']+microtime());
define('SITE_ROOT', dirname(__FILE__) . '/', true);
define('FW_DIR', 'tsFramework/');
define('RSC_DIR', 'tsFramework/resources/');

// Initalize ErrorHandler
require SITE_ROOT . 'tsFramework/modules/exceptions/FrameworkException.class.php';
require SITE_ROOT . 'tsFramework/modules/exceptions/PHPException.class.php';
require SITE_ROOT . 'tsFramework/modules/core/ErrorHandler.class.php';
$errorHandler = new ErrorHandler();
$errorHandler->register();

// Initialize Autoloader
require SITE_ROOT . 'tsFramework/modules/core/Observable.class.php';
require SITE_ROOT . 'tsFramework/modules/core/Autoloader.class.php';
$autoloader = new Autoloader();
$autoloader->register();

$settings = new Settings();
$autoloader->addObserver($settings);

$core = new Core();
$core->setSettings($settings);
$core->handleRequest();

?>
