<?php

abstract class PageController {
	/** @var Core */
	protected $core;
	
	abstract public function generate();
	abstract public function show();
	
	public function setCore($core) {
		$this->core = $core;
	}
}

?>