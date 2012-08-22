<?php

/**
 * Description of RequestHandler
 *
 * @author Pascal Münst
 */
class RequestHandler {

    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';

    private $requestArray;
    private $requestUri;
    private $requestProtocol;
    private $requestMethod;
    private $requestReferer;
	private $requestDomain;
	private $gzipEnabled;

    public function __construct() {
		$this->requestUri = $_SERVER['REQUEST_URI'];
		$this->requestArray = self::parseRequestArray($this->requestUri);
		$this->requestProtocol = (array_key_exists('HTTPS', $_SERVER) === true && $_SERVER['HTTPS'] === 'on') ? self::PROTOCOL_HTTPS : self::PROTOCOL_HTTP;
		$this->requestMethod = $_SERVER['REQUEST_METHOD'];
		$this->requestReferer = (array_key_exists('HTTP_REFERER', $_SERVER)) ? $_SERVER['HTTP_REFERER'] : null;

		$this->requestDomain = $_SERVER['SERVER_NAME'];
		$this->gzipEnabled = false;
		
		if(substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') > 0)
			$this->gzipEnabled = true;
    }
	
	public function output_send(){
		if (!headers_sent() && error_get_last()==NULL ) {
			return false;

		}
		return true;
	}
	
	public function handleRequest() {
		
		if($this->gzipEnabled === true) {
			ob_start('ob_gzhandler');
		} else { 
			ob_start();
		}
		ob_implicit_flush(false);
		
		$core = new Core($this);
		$core->loadManifest();
		
		//@TODO: File oder nicht, forbidden oder nicht?
		if($this->requestArray['fileName'] === null && count($this->requestArray['path']) === 0) {
			$domains = $core->getSettings()->getValue('tsfw_domains');
			self::redirect($domains[$this->requestDomain]->getStartPage());
		}
		
		$pages = $core->getSettings()->getValue('tsfw_sites');
		
		if(array_key_exists(substr($this->requestUri,1), $pages) === true) {
			$domains = $core->getSettings()->getValue('tsfw_domains');
			
			header('Content-Type: text/html; charset=UTF-8');
			header('Content-language: ' . substr($domains[$core->getRequestHandler()->getRequestDomain()]->getLocale(),0,2));
			
			$core->processPage($pages[substr($this->requestUri,1)]);
		} else {
			ErrorHandler::displayHttpError(404);
		}
		
		$contentHash = md5(ob_get_contents());
		$requestHeaders = apache_request_headers();
		
		// ETag
		if(array_key_exists('If-None-Match', $requestHeaders) === true && $requestHeaders['If-None-Match'] === $contentHash) {
			header('HTTP/1.1 304 Not Modified');
			exit;
		} else {
			header('ETag: ' . $contentHash);
		}
		
		ob_end_flush();
		exit;
	}
	
    private static function parseRequestArray($reqUri) {
		$reqArray = array(
			 'path' => array()
			,'fileName' => null
			,'fileExt' => null
			,'params' => array()
		);
		
		
		$reqUriCleaned = substr($reqUri, 1);
		
		if($reqUriCleaned === false)
			return $reqArray;
			
		$pathArr = explode('/', $reqUriCleaned);
		$reqFile = array_pop($pathArr);
		
		$reqArray['path'] = $pathArr;

		$reqFileParams = StringUtils::before($reqFile, '.');
		$reqArray['fileExt'] = StringUtils::after($reqFile, '.');

		$reqFileParamsArr = explode('-', $reqFileParams);
		$reqArray['fileName'] = array_shift($reqFileParamsArr);
		
		$reqArray['params'] = $reqFileParamsArr;

		return $reqArray;
    }
	
	public function redirect($uri) {
		header('HTTP/1.1 302 Moved Temporarily'); 
		header('Location: ' . $uri);
		exit;
	}

	public function getRequestArray() {
		return $this->requestArray;
	}

    public function getRequestParam($index) {
		if (array_key_exists($index, $this->requestArray['params']) === false)
			return null;

		return $this->requestArray['params'][$index];
    }

    public function getRequestURI() {
		return $this->requestUri;
    }
	
	public function getRequestDomain() {
		return $this->requestDomain;
	}

}

?>