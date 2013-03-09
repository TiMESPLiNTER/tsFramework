<?php
namespace ch\timesplinter\core;

/**
 * Description of Site
 *
 * @author pascal91
 */
class PageNew {
	public function __construct($controller, $method) {
		$this->controller = $controller;
		$this->method = $method;
	}
	
	private $controller;
	private $method;
	public $sslRequired;
}

?>
