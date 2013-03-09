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
	public static function matchRoutes(stdClass $routes, $uri) {
		$routeEntries = null;
		
		foreach($routes as $routeID => $r) {
			$routeObj = self::createRouteObject($routeID, $r);
			
			if(preg_match($routeObj->pattern, $uri) === 0)
				continue;
			
			$routeEntries[$routeObj->method] = self::createRouteObject($routeID, $r);
		}
		
		if(count($routeEntries) === null)
			return null;
		
		return $routeEntries;
	}
	
	public static function getRouteById(SimpleXMLElement $routes, $matches) {
		$routeEntry = $routes->xpath("//route[@id='" . $matches . "']");
		
		if(!isset($routeEntry[0]))
			return null;
		
		return self::createRouteObject($routeEntry[0]);
	}
	
	private static function createRouteObject($routeID, stdClass $routeEntry) {
		$route = new Route;
		
		$pattern = str_replace(array('/'), array('\/'), $routeEntry->pattern);
		
		$route->method = Route::METHOD_UNKNOWN;
		$route->sslRequired = isset($routeEntry->sslRequired)?$routeEntry->sslRequired:false;
		
		$routeEntryMethod = $routeEntry->method;
		
		if($routeEntryMethod === 'GET')
			$route->method = Route::METHOD_GET;
		elseif($routeEntryMethod === 'POST')
			$route->method = Route::METHOD_POST;
		
		$route->pattern = '/^' . $pattern . '$/';
		
		
		$ctrlParts = explode(':',$routeEntry->controller);
		
		$methodName = array_pop($ctrlParts);
		
		$route->id = $routeID;
		$route->controllerClass = implode('\\',$ctrlParts);
		$route->controllerMethod = $methodName;
		
		return $route;
	}
}

/* EOF */