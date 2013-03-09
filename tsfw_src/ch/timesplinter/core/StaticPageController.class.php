<?php
namespace ch\timesplinter\core;

use ch\timesplinter\logger\LoggerFactory;

/**
 * Description of StaticPageController
 *
 * @author pascal91
 */
class StaticPageController extends PageController {
	private  $logger;
	
	public function __construct() {
		$this->logger = LoggerFactory::getLoggerByName('dev', __CLASS__);
	}

	/**
	 * 
	 * @return \HttpResponse
	 */
	public function getPage() {
		//$db = \ch\timesplinter\db\DBFactory::getNewInstance(DB::TYPE_MYSQL, $dbConnect);
		$domains = $this->core->getSettings()->getValue('core')->domains;
		
		$html = self::render($this->route->id, array('siteTitle' => 'Start Page'));
		//var_dump($domains[$this->core->getRequestHandler()->getRequestDomain()]->locale);
		$headers = array(
			 'Content-Type' => 'text/html; charset=UTF-8'
			,'Content-Language' => substr(DomainUtils::getDomainInfo($domains, $this->httpRequest->getHost())->locale,0,2)
		);
		
		return new HttpResponse(200, $html, $headers);
	}
	
	public function getErrorPage($errorCode, $errorMsg) {
		$domains = $this->core->getSettings()->getValue('core')->domains;
		$currentDomain = DomainUtils::getDomainInfo($domains,$this->httpRequest->getHost());
		//echo $errorCode; exit;
		if($currentDomain === null) {
			$html = '<html><body><h1>Error ' . $errorCode . '</h1><p>' . $errorMsg . '</p></body></html>';
		} else {		
			$html = self::render('error', array('siteTitle' => 'Error ' . $errorCode, 'error_msg' => $errorMsg));
		}
			
		//var_dump($domains[$this->core->getRequestHandler()->getRequestDomain()]->locale);
		$headers = array(
			 'Content-Type' => 'text/html; charset=UTF-8'
			,'Content-Language' => ($currentDomain !== null)?substr($currentDomain->locale,0,2):'en_US'
		);
		
		return new HttpResponse($errorCode, $html, $headers);
	}
	
	public function testPHPCache() {
		$newPage = new PageNew('SampleController','theMethod');
		$newPage->sslRequired = true;
		$arr = array(
			 $newPage
			,'hallo welt'
			,200
			,true
			,800.8
			,array('pear' => 'birne', 'apfel', 'maracuja')
			,false
		);
		
		$cache = CacheFactory::getInstance(CacheFactory::TYPE_PHPCACHE);
		
		$cache->storeValue('test', $arr);
		$cache->storeValue(12, 'hello world');
		
		$headers = array(
			 'Content-Type' => 'text/html; charset=UTF-8'
		);
		
		$cache->save();
		
		$cache->load();
		
		ob_start();
		//var_dump($cache->getAllValues());
		$values = ob_get_clean();
		
		$cool = '<pre>' .  $values . '</pre>';
		
		return new HttpResponse(200, 'PHP cache tested visit log!' . $cool, $headers);
	}
}

?>