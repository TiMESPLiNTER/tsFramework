<?php
namespace ch\timesplinter\core;

use \SimpleXMLElement;
use \stdClass;
use \ch\timesplinter\core\HttpRequest;

/**
 * Description of RouteUtils
 *
 * @author pascal91
 */
class RouteUtils {
	public static function matchRoutesAgainstPath(stdClass $routes, HttpRequest $httpRequest) {
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
	
	private static function createRouteObject($routeID, stdClass $routeEntry) {
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

	public static function getFirstRouteWhichHasMethod($routes, $method) {
		foreach($routes as $r) {
			if(isset($r->methods[$method]) === true)
				return $r;
		}

		return null;
	}
}

/* EOF */