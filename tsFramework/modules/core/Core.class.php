<?php

/**
 * Description of Core
 *
 * @author pascal91
 */
class Core {
	/** @var Settings */
	private $settings;
	/** @var RequestHandler */
	private $requestHandler;
	/** @var LocaleHandler */
	private $localeHandler;
	
	public function __construct() {
		$this->requestHandler = new RequestHandler();
		$this->localeHandler = new LocaleHandler($this);
	}
	
	public function handleRequest() {
		ob_start();
		ob_implicit_flush(false);
		
		$requestArray = $this->requestHandler->getRequestArray();
		
		//@TODO: File oder nicht, forbidden oder nicht?
		if($requestArray['fileName'] === null && count($requestArray['path']) === 0) {
			$domains = $this->settings->getValue('domains','core');
			RequestHandler::redirect($domains[$this->requestHandler->getRequestDomain()]->startpage);
		}
		
		$pages = $this->settings->getValue('pages','core');
		$filePath = substr($requestArray['filePath'], 1);

		if(isset($pages[$filePath]) === true) {
			$domains = $this->settings->getValue('domains','core');
			
			header('Content-Type: text/html; charset=UTF-8');
			header('Content-language: ' . substr($domains[$this->requestHandler->getRequestDomain()]->locale,0,2));
			
			self::processPage($pages[$filePath]);
		} else {
			ErrorHandler::displayHttpError(404);
		}
		
		$content = ob_get_contents();
		$contentHash = md5($content);
		
		// ETag
		if(isset($_SERVER['HTTP_IF_NONE_MATCH']) === true && $_SERVER['HTTP_IF_NONE_MATCH'] === $contentHash) {
			header('HTTP/1.1 304 Not Modified');
			exit;
		} else {
			header('Etag: ' . $contentHash);
		}
		
		ob_end_flush();
		exit;
	}
	
	/**
	 * 
	 * @param Page $site
	 */
	public function processPage($site) {
		if($site->sslRequired === true && $this->requestHandler->isConnectionSSL() === false)
			$this->requestHandler->provideSecureConnection();
		elseif($site->sslRequired === false && $this->requestHandler->isConnectionSSL() === true)
			$this->requestHandler->provideInsecureConnection();
		
		$this->localeHandler->localize();
		
		$c = $site->controller;
		$controller = new $c;
		
		$controller->prepare($this);
		$controller->generate();
		$controller->show();
		
	}
	
	/**
	 * 
	 * @return Settings
	 */
	public function getSettings() {
		return $this->settings;
	}
	
	public function setSettings(Settings $settings) {
		$this->settings = $settings;
	}
	
	/**
	 * 
	 * @return RequestHandler
	 */
	public function getRequestHandler() {
		return $this->requestHandler;
	}
}

?>
