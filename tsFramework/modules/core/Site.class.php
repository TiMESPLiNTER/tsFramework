<?php

/**
 * Description of Site
 *
 * @author pascal91
 */
class Site {
	private $path;
	private $controller;
	private $sslRequired;
	
	public function __construct($path, $controller, $sslRequired) {
		$this->path = $path;
		$this->controller = $controller;
		$this->sslRequired = $sslRequired;
	}
	
	/**
	 * 
	 * @return PageController
	 */
	public function getController() {
		return $this->controller;
	}
	
	public function getSSLRequired() {
		return $this->sslRequired;
	}
}

?>
