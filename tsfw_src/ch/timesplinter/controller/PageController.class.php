<?php

namespace ch\timesplinter\controller;

use ch\timesplinter\common\View;
use ch\timesplinter\core\Core;
use ch\timesplinter\core\HttpRequest;
use ch\timesplinter\core\Route;
use ch\timesplinter\core\HttpResponse;
use ch\timesplinter\view\PageView;

/**
 * The standard PageController loads the defined template for the current
 * domain and print it out
 * 
 * @author Pascal Münst
 * @copyright (c) 2012, Pascal Münst
 * @version 1.0
 */
abstract class PageController extends FrameworkController {
	/** @var View */
	protected $view;

	public function __construct(Core $core, HttpRequest $httpRequest, Route $route) {
		parent::__construct($core, $httpRequest, $route);

		$this->view = new PageView($this);
	}
	
	protected function generateHttpResponse($httpStatusCode = 200, $html = null, $headers = array()) {
		$headers['Content-Type'] = 'text/html; charset=UTF-8';
        $headers['Content-Language'] = substr($this->core->getLocaleHandler()->getLocale(),0,2);
		
		return new HttpResponse($httpStatusCode, $html, $headers);
	}
}

/* EOF */