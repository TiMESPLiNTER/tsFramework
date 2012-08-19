<?php

error_reporting(E_ALL);

define('siteRoot', dirname(__FILE__) . '/', true);
define('fwDir', 'tsFramework/');

// Initialize autoloader
require siteRoot . 'tsFramework/classes/core/Autoloader.class.php';
$autoloader = new Autoloader();
$autoloader->register();


$requestHandler = new RequestHandler();
$requestHandler->handleRequest();

?>
