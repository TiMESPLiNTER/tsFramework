<?php
namespace ch\timesplinter\controller;

use ch\timesplinter\core\Route;
use ch\timesplinter\core\HttpRequest;
use ch\timesplinter\core\Core;

/**
 * Description of FrameworkController
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright (c) 2012, Pascal Münst
 */
abstract class FrameworkController {
	/** @var Core */
	protected $core;
	/** @var HttpRequest */
	protected $httpRequest;
	/** @var Route */
	protected $route;
	protected $currentDomain;
	
	public function __construct(Core $core, HttpRequest $httpRequest, Route $route) {
		$this->core = $core;
		$this->httpRequest = $httpRequest;
		$this->route = $route;
		
		$host = $this->httpRequest->getHost();
		$this->currentDomain = isset($this->core->getSettings()->core->domains->$host)?$this->core->getSettings()->core->domains->$host:null;
	}
	
	public function setCore($core) {
		$this->core = $core;
	}
	
	public function setHttpRequest(HttpRequest $httpRequest) {
		$this->httpRequest = $httpRequest;
	}
	
	public function setRoute(Route $route) {
		$this->route = $route;
	}
}

/* EOF */