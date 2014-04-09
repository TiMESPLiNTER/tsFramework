<?php

namespace ch\timesplinter\view;

use ch\timesplinter\controller\FrameworkController;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2014, TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
abstract class View {
	protected $controller;

	public function __construct(FrameworkController $controller) {
		$this->controller = $controller;
	}

	abstract public function render($tplFile, array $tplVars);
}

/* EOF */