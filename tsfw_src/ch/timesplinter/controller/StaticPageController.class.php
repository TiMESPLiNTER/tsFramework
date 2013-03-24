<?php

namespace ch\timesplinter\controller;

use ch\timesplinter\core\FrameworkLogger;
use ch\timesplinter\core\FrameworkLoggerFactory;
use ch\timesplinter\logger\LoggerFactory;
use ch\timesplinter\core\Core;
use ch\timesplinter\core\HttpRequest;
use ch\timesplinter\core\HttpResponse;
use ch\timesplinter\core\Route;
use ch\timesplinter\logger\TSLogger;

/**
 * Description of StaticPageController
 *
 * @author pascal91
 */
class StaticPageController extends PageController {
	private  $logger;
	
	public function __construct(Core $core, HttpRequest $httpRequest, Route $route) {
		parent::__construct($core, $httpRequest, $route);

		$this->logger = FrameworkLoggerFactory::getLogger($this);
	}

	/**
	 * 
	 * @return \HttpResponse
	 */
	public function getPage() {
		$pageData = $this->core->getSettings()->pagedata;
		$routeID = $this->route->id;

		$this->logger->info('Requested route: ' . $routeID);
		$this->logger->debug('Route info', array($this->route));

		if(isset($pageData->$routeID->active))
			$this->activeHtmlIds = $pageData->$routeID->active;
		
		$html = $this->render($routeID, array(
			'siteTitle' => isset($pageData->$routeID->title)?$pageData->$routeID->title:null,
			'runtime' => round(microtime(true) - REQUEST_TIME,3) ,
            'locale' => $this->core->getLocaleHandler()->getLocale(),
            'timezone' => date_default_timezone_get()
		));

		$headers = array(
			 'Content-Type' => 'text/html; charset=UTF-8'
			,'Content-Language' => $this->core->getLocaleHandler()->getLanguage()
		);
		
		return new HttpResponse(200, $html, $headers);
	}
	
	public function getErrorPage($errorCode, $errorMsg) {
		$html = $this->render('error', array(
			'siteTitle' => 'Error ' . $errorCode,
			'error_code' => $errorCode,
			'error_msg' => $errorMsg
		));

		$headers = array(
			 'Content-Type' => 'text/html; charset=UTF-8'
			,'Content-Language' => $this->core->getLocaleHandler()->getLanguage()
		);
		
		return new HttpResponse($errorCode, $html, $headers);
	}
}

/* EOF */