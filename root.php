<?php

define('siteRoot', dirname(__FILE__) . '/', true);
define('fwDir', 'tsFramework/');

// Initialize autoloader
require siteRoot . 'tsFramework/classes/core/Autoloader.class.php';
$autoloader = new Autoloader();
$autoloader->register();


$requestHandler = RequestHandler::getInstance();

?>
