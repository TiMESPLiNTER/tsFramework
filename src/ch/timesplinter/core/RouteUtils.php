<?php

namespace ch\timesplinter\core;

use \stdClass;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2014 by TiMESPLiNTER Webdevelopment
 */
class RouteUtils
{
	public static function matchRoutesAgainstPath(stdClass $routes, HttpRequest $httpRequest)
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

		if(count($routeEntries) === 0)
			return null;

		return $routeEntries;
	}

	protected static function createRouteObject($routeID, stdClass $routeEntry)
	{
		$route = new Route;

		$route->sslRequired = isset($routeEntry->sslRequired)?$routeEntry->sslRequired:false;
		$route->sslForbidden = isset($routeEntry->sslForbidden)?$routeEntry->sslForbidden:false;
		$route->methods = array();

		foreach($routeEntry->methods as $method => $controller) {
			$routeMethodTmp = new RouteMethod();

			$ctrlParts = explode(':', $controller);
			$methodName = array_pop($ctrlParts);

			$routeMethodTmp->controllerClass = implode('\\', $ctrlParts);
			$routeMethodTmp->controllerMethod = $methodName;
			$routeMethodTmp->method = $method;

			$route->methods[$method] = $routeMethodTmp;
		}

		$route->pattern = '@^' . $routeEntry->pattern . '$@';
		$route->id = $routeID;

		return $route;
	}

	public static function getFirstRouteWhichHasMethod($routes, $method)
	{
		foreach($routes as $r) {
			if(isset($r->methods[$method]) === true)
				return $r;
		}

		return null;
	}


	public static function filterRoutesByMethod($routes, $method)
	{
		return array_filter($routes, function() use($method) {
			/** @var Route $r */
			return !(isset($r->methods[$method]) === true || isset($r->methods['*']) === true);
		});
	}
}

/* EOF */