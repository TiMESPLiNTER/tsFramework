<?php

/**
 * Description of RequestHandler
 *
 * @author Pascal Münst
 */
class RequestHandler {
    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';

	private $logger;
	
    private $requestArray;
    private $requestUri;
    private $requestProtocol;
    private $requestMethod;
    private $requestReferer;
	private $requestDomain;

    public function __construct() {
		$this->logger = LoggerFactory::getLoggerByName('dev', $this);
		
		$this->requestUri = $_SERVER['REQUEST_URI'];
		$this->requestArray = self::parseRequestArray($this->requestUri);
		$this->requestProtocol = (isset($_SERVER['HTTPS']) === true && $_SERVER['HTTPS'] === 'on') ? self::PROTOCOL_HTTPS : self::PROTOCOL_HTTP;
		$this->requestMethod = $_SERVER['REQUEST_METHOD'];
		$this->requestReferer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;

		$this->requestDomain = $_SERVER['SERVER_NAME'];
    }
	
    private static function parseRequestArray($reqUri) {
		$reqArray = array(
			 'path' => array()
			,'fileName' => null
			,'fileExt' => null
			,'params' => array()
		);
		
		$reqUriParts = explode('?',$reqUri);
		
		$reqUriCleaned = substr($reqUriParts[0], 1);
		
		if($reqUriCleaned === false)
			return $reqArray;
			
		$pathArr = explode('/', $reqUriCleaned);
		$reqFile = array_pop($pathArr);
		
		$reqArray['path'] = $pathArr;

		$reqFileParams = StringUtils::beforeLast($reqFile, '.');
		$reqArray['fileExt'] = StringUtils::afterLast($reqFile, '.');

		$reqFileParamsArr = explode('-', $reqFileParams);
		$reqArray['fileName'] = array_shift($reqFileParamsArr);
		
		$reqArray['filePath'] = implode('/',$pathArr) . (($reqArray['fileName'] !== null)?'/' . $reqArray['fileName']:'') . (($reqArray['fileExt'] !== null)?'.' . $reqArray['fileExt']:'');
		
		$reqArray['params'] = array_merge($reqFileParamsArr,$_GET);
		
		return $reqArray;
    }
	
	public static function redirect($uri) {
		header('HTTP/1.1 302 Moved Temporarily'); 
		header('Location: ' . $uri);
		exit;
	}
	
	public function provideSecureConnection() {
		self::redirect(self::PROTOCOL_HTTPS . '://' . $this->requestDomain . $this->requestUri);
	}
	
	public function provideInsecureConnection() {
		self::redirect(self::PROTOCOL_HTTP . '://' . $this->requestDomain . $this->requestUri);
	}

	public function getRequestArray() {
		return $this->requestArray;
	}

    public function getRequestParam($index) {
		if(array_key_exists($index, $this->requestArray['params']) === false)
			return null;

		return $this->requestArray['params'][$index];
    }

    public function getRequestURI() {
		return $this->requestUri;
    }
	
	public function getRequestDomain() {
		return $this->requestDomain;
	}

	public function isConnectionSSL() {
		if($this->requestProtocol === self::PROTOCOL_HTTPS)
			return true;
		
		return false;
	}
}

?>