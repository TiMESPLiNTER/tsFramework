<?php

define('siteRoot', dirname(__FILE__) . '/', true);
define('fwDir', 'tsFramework/');

// Initialize autoloader
require siteRoot . 'tsFramework/classes/core/Autoloader.class.php';
Autoloader::init();


$requestHandler = RequestHandler::getInstance();

?>
