<?php

abstract class PageController {
	/** @var Core */
	protected $core;
	/** @var TemplateEngine */
	protected $tplEngine;


	abstract public function generate();
	abstract public function show();
	
	public function setCore($core) {
		$this->core = $core;
	}
}

?>