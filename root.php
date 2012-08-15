<?php

define('siteRoot', dirname(__FILE__) . '/', true);
define('fwDir', 'tsFramework/');

// Initialize autoloader
require siteRoot . 'tsFramework/classes/core/Autoloader.class.php';
Autoloader::init();

/** @var TurboCache */
$tcache = TurboCacheFactory::getInstance();
$tcache->init();

$tmp_object = new stdClass;
$tmp_object->str_attr = array('test' => 'välüe', 20);
$tmp_object->int_attr = 123;

$tcache->storeValue('sampleKey', $tmp_object);
$tcache->storeValue('date', new DateTime('2012-08-20'));

echo $_SERVER['REQUEST_URI'];

$tcache->close();


// Load again
$start = microtime(true);
$countIt = 0;

	$tcache->init();

	$obj = (object)$tcache->getValue('sampleKey');
	$date = $tcache->getValue('date');
	
	echo'<pre>'; var_dump($obj,$date); echo'</pre>';

	
	$tcache->close();

//echo 'time taken: ' , (microtime(true)-$start) , ' secs - it\'s ' , $countIt;

?>