<?php

error_reporting(E_ALL);

define('REQUEST_TIME', $_SERVER['REQUEST_TIME']+microtime());
define('SITE_ROOT', dirname(__FILE__) . '/', true);
define('FW_DIR', 'tsFramework/');
define('RSC_DIR', 'resources/');

// Initalize ErrorHandler
require SITE_ROOT . 'tsFramework/modules/core/ErrorHandler.class.php';
$errorHandler = new ErrorHandler();
$errorHandler->register();

// Initialize Autoloader
require SITE_ROOT . 'tsFramework/modules/core/Autoloader.class.php';
$autoloader = new Autoloader();
$autoloader->register();

$requestHandler = new RequestHandler();
$requestHandler->handleRequest();

?>
