<?php

namespace ch\timesplinter\plugins\robots;

use \ch\timesplinter\core\FrameworkPlugin;
use \stdClass;
use \ch\timesplinter\core\HttpResponse;

/**
 * Description of Robots
 *
 * @author pascal91
 */
class Robots extends FrameworkPlugin {
	//put your code here
	public function beforeRequestBuilt() {
		$pluginSettings = $this->core->getSettings()->{'plugin.robots'};
		
		//if(isset($pluginSettings->environments->))
		
		$routes = $this->core->getSettings()->core->routes;
		
		$robotsRoute = new stdClass;
		$robotsRoute->method = 'GET';
		$robotsRoute->controller = 'ch:timesplinter:plugins:robots:Robots:displayRobots';
		$robotsRoute->pattern = '/robots.txt';
		
		$routes->__robots = $robotsRoute;
		
		//var_dump($this->core->getSettings()->core->routes); exit;
	}
	
	public function displayRobots() {
		/*User-agent: *
		Disallow: */
		return new HttpResponse(200, 'noindex,nofollow', array(
			'Content-Type', 'plain/text'
		));
	}
}

/* EOF */
