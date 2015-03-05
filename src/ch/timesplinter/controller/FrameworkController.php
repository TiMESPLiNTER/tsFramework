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
	/** @var null|\stdClass */
	protected $currentDomain;
	
	public function __construct(Core $core, HttpRequest $httpRequest, Route $route)
	{
		$this->core = $core;
		$this->httpRequest = $httpRequest;
		$this->route = $route;
		
		$this->currentDomain = $this->core->getCurrentDomain();
	}

	/**
	 * @return Core
	 */
	public function getCore()
	{
		return $this->core;
	}

	/**
	 * @return HttpRequest
	 */
	public function getHttpRequest()
	{
		return $this->httpRequest;
	}

	/**
	 * @return Route
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * @param Route $route
	 */
	public function setRoute(Route $route)
	{
		$this->route = $route;
	}

	/**
	 * @return null|\stdClass
	 */
	public function getCurrentDomain() 
	{
		return $this->currentDomain;
	}
}

/* EOF */