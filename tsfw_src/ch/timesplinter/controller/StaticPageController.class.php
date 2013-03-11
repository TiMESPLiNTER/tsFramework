<?php
namespace ch\timesplinter\controller;

use ch\timesplinter\logger\LoggerFactory;
use ch\timesplinter\core\Core;
use ch\timesplinter\core\HttpRequest;
use ch\timesplinter\core\HttpResponse;
use ch\timesplinter\core\Route;

/**
 * Description of StaticPageController
 *
 * @author pascal91
 */
class StaticPageController extends PageController {
	private  $logger;
	
	public function __construct(Core $core, HttpRequest $httpRequest, Route $route) {
		parent::__construct($core, $httpRequest, $route);
		
		$this->logger = LoggerFactory::getLoggerByName('dev', __CLASS__);
	}

	/**
	 * 
	 * @return \HttpResponse
	 */
	public function getPage() {
		$pageData = $this->core->getSettings()->pagedata;
		$routeID = $this->route->id;
		
		if(isset($pageData->$routeID->active))
			$this->activeHtmlIds = $pageData->$routeID->active;
		
		$html = self::render($routeID, array(
			'siteTitle' => isset($pageData->$routeID->title)?$pageData->$routeID->title:null
		));
		//var_dump($domains[$this->core->getRequestHandler()->getRequestDomain()]->locale);
		$headers = array(
			 'Content-Type' => 'text/html; charset=UTF-8'
			,'Content-Language' => $this->core->getLocaleHandler()->getLanguage()
		);
		
		return new HttpResponse(200, $html, $headers);
	}
	
	public function getErrorPage($errorCode, $errorMsg) {
		/*$domains = $this->core->getSettings()->getValue('core')->domains;
		$currentDomain = DomainUtils::getDomainInfo($domains,$this->httpRequest->getHost());
		//echo $errorCode; exit;
		if($currentDomain === null) {
			$html = '<html><body><h1>Error ' . $errorCode . '</h1><p>' . $errorMsg . '</p></body></html>';
		} else {		*/
			$html = self::render('error', array('siteTitle' => 'Error ' . $errorCode, 'error_msg' => $errorMsg));
		//}
			
		//var_dump($domains[$this->core->getRequestHandler()->getRequestDomain()]->locale);
		$headers = array(
			 'Content-Type' => 'text/html; charset=UTF-8'
			,'Content-Language' => $this->core->getLocaleHandler()->getLanguage()
		);
		
		return new HttpResponse($errorCode, $html, $headers);
	}
}

?>