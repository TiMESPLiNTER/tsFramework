<?php

namespace ch\timesplinter\core;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2014 by TiMESPLiNTER Webdevelopment
 */
class RouteUtils
{
	/**
	 * @param \stdClass $routes
	 * @param HttpRequest $httpRequest
	 * @return Route[]
	 */
	public static function matchRoutesAgainstPath(\stdClass $routes, HttpRequest $httpRequest)
	{
		$routeEntries = array();

		foreach($routes as $routeID => $r) {
			$routeObj = self::createRouteObject($routeID, $r);

			if(preg_match($routeObj->pattern, $httpRequest->getPath(), $params) === 0)
				continue;

			array_shift($params);
			$routeObj->setParams($params);

			if(!isset($routeObj->methods['HEAD']) && isset($routeObj->methods['GET']))
				$routeObj->methods['HEAD'] = $routeObj->methods['GET'];

			$routeEntries[$routeObj->id] = $routeObj;
		}

		return $routeEntries;
	}

	/**
	 * @param int $routeID
	 * @param \stdClass $routeEntry
	 * @return Route
	 */
	protected static function createRouteObject($routeID, \stdClass $routeEntry)
	{
		$route = new Route();

		$route->sslRequired = isset($routeEntry->sslRequired)?$routeEntry->sslRequired:false;
		$route->sslForbidden = isset($routeEntry->sslForbidden)?$routeEntry->sslForbidden:false;
		$route->methods = array();

		foreach($routeEntry->methods as $method => $controller) {
			$routeMethodTmp = new RouteMethod();

			$controllerInfo = FrameworkUtils::stringToClassName($controller);

			$routeMethodTmp->controllerClass = $controllerInfo->className;
			$routeMethodTmp->controllerMethod = $controllerInfo->methodName;
			$routeMethodTmp->method = $method;

			$route->methods[$method] = $routeMethodTmp;
		}

		$route->pattern = '@^' . str_replace('@', '\@', $routeEntry->pattern) . '$@';
		$route->id = $routeID;

		return $route;
	}

	/**
	 * @param Route[] $routes
	 * @param string $method
	 * @return Route|null
	 */
	public static function getFirstRouteWhichHasMethod($routes, $method)
	{
		foreach($routes as $r) {
			if(isset($r->methods[$method]) === true)
				return $r;
		}

		return null;
	}

	/**
	 * @param Route[] $routes
	 * @param string $method
	 * @return Route[]
	 */
	public static function filterRoutesByMethod($routes, $method)
	{
		return array_filter($routes, function($route) use($method) {
			return !(isset($route->methods[$method]) === false && isset($route->methods['*']) === false);
		});
	}
}

/* EOF */