<?php

error_reporting(E_ALL);

define('siteRoot', dirname(__FILE__) . '/', true);
define('fwDir', 'tsFramework/');

// Initialize autoloader
require siteRoot . 'tsFramework/classes/core/Autoloader.class.php';
Autoloader::init();

$requestHandler = new RequestHandler();
$requestHandler->handleRequest();

?>
