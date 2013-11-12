<?php
namespace ch\timesplinter\core;

use \SimpleXMLElement;
use \stdClass;

/**
 * Description of RouteUtils
 *
 * @author pascal91
 */
class RouteUtils {
	public static function matchRoutesAgainstPath(stdClass $routes, HttpRequest $httpRequest) {
		$routeEntries = null;
		
		foreach($routes as $routeID => $r) {
			$routeObj = self::createRouteObject($routeID, $r);
			
			if(preg_match($routeObj->pattern, $httpRequest->getPath(), $params) === 0)
				continue;

			array_shift($params);
			$routeObj->setParams($params);

			$routeEntries[$routeObj->method] = self::createRouteObject($routeID, $r);
		}
		
		if(count($routeEntries) === null)
			return null;
		
		return $routeEntries;
	}
	
	private static function createRouteObject($routeID, stdClass $routeEntry) {
		$route = new Route;
		
		$route->method = Route::METHOD_UNKNOWN;
		$route->sslRequired = isset($routeEntry->sslRequired)?$routeEntry->sslRequired:false;
		$route->sslForbidden = isset($routeEntry->sslForbidden)?$routeEntry->sslForbidden:false;

		$routeEntryMethod = $routeEntry->method;
		
		$route->method = $routeEntryMethod;
		$route->pattern = '@^' . $routeEntry->pattern . '$@';
		
		$ctrlParts = explode(':',$routeEntry->controller);
		
		$methodName = array_pop($ctrlParts);
		
		$route->id = $routeID;
		$route->controllerClass = implode('\\', $ctrlParts);
		$route->controllerMethod = $methodName;
		
		return $route;
	}
}

/* EOF */