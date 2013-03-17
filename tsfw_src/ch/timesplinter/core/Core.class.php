<?php
namespace ch\timesplinter\core;

use ch\timesplinter\common\StringUtils;
use ch\timesplinter\logger\LoggerFactory;
use ch\timesplinter\core\SessionHandler;
use ch\timesplinter\core\Settings;
use \DateTime;
use ch\timesplinter\logger\TSLogger;

/**
 * Description of Core
 *
 * @author pascal91
 */
class Core {
	private $logger;
	/** @var Settings */
	private $settings;
	/** @var RequestHandler */
	private $requestHandler;
	/** @var LocaleHandler $localeHandler */
	private $localeHandler;
	/** @var SessionHandler */
	private $sessionHandler;
	/** @var ErrorHandler */
	private $errorHandler;
	private $plugins;
	
	/** @var \HttpRequest */
	private $httpRequest;
	/** @var \HttpResponse */
	private $httpResponse;
	
	public function __construct() {
        $this->errorHandler = new ErrorHandler($this);
        $this->errorHandler->register();

		$this->settings = new Settings(SETTINGS_DIR, array(
            'fw_dir' => FW_DIR,
            'site_root' => SITE_ROOT
        ));
		$this->localeHandler = new LocaleHandler($this);
		$this->sessionHandler = new SessionHandler($this);
		$this->logger = TSLogger::getLoggerByName('dev', $this);
		
		$plugins = $this->settings->core->plugins;
		
		foreach($plugins as $plugin) {
			$pluginClass = str_replace(':','\\',$plugin);
			
			$this->plugins[] = new $pluginClass($this);
		}
	}
	
	/**
	 * 
	 * @return HttpRequest
	 */
	private function createHttpRequest() {
		$protocol = (isset($_SERVER['HTTPS']) === true && $_SERVER['HTTPS'] === 'on') ? HttpRequest::PROTOCOL_HTTPS : HttpRequest::PROTOCOL_HTTP;
		$uri = $_SERVER['REQUEST_URI'];
		$path = StringUtils::beforeLast($uri, '?');
		
		$languages = array();
		$langsRates = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		
		foreach($langsRates as $lr) {
			$lrParts = explode(';', $lr);
			
			$languages[$lrParts[0]] = isset($lrParts[1])?(float)StringUtils::afterFirst($lrParts[1], 'q='):1.0;
		}
		
		$requestTime = new DateTime();
		$requestTime->setTimestamp($_SERVER['REQUEST_TIME']);
		
		$httpRequest = new HttpRequest();
		
		$httpRequest->setHost($_SERVER['SERVER_NAME']);
		//$httpRequest->setParams($params);
		$httpRequest->setPath($path);
		$httpRequest->setPort($_SERVER['SERVER_PORT']);
		$httpRequest->setProtocol($protocol);
		$httpRequest->setQuery($_SERVER['QUERY_STRING']);
		$httpRequest->setURI($uri);
		
		$httpRequest->setRequestTime($requestTime);
		$httpRequest->setRequestMethod($_SERVER['REQUEST_METHOD']);
		$httpRequest->setUserAgent($_SERVER['HTTP_USER_AGENT']);
		$httpRequest->setLanguages($languages);
		
		return $httpRequest;
	}
	
	public function handleRequest() {
		$this->sessionHandler->start();
		
		$this->invokePluginHook('beforeRequestBuilt');
		
		$this->httpRequest = $this->createHttpRequest();

        $this->invokePluginHook('afterRequestBuilt');

        $this->localeHandler->localize($this->httpRequest);

		$currentDomain = DomainUtils::getDomainInfo($this->settings->core->domains, $this->httpRequest->getHost());
		
		if($currentDomain === null) {
			if(isset($this->settings->defaults->domain))
				RequestHandler::redirect('http://' . $this->settings->defaults->domain);
			
			return $this->errorHandler->displayHttpError(500, $this->httpRequest);
		}
		
		if($this->httpRequest->getPath() === '/') {
			// Load the default page as "virtual" httpRequest for the base url (no uri)
			$routeId = (string)$currentDomain->startroute;
			RequestHandler::redirect($routeId);
		}
		
		$matchedRoute = RouteUtils::matchRoutes($this->settings->core->routes, $this->httpRequest->getPath());
				
		if($matchedRoute === null)
			return $this->errorHandler->displayHttpError(404, $this->httpRequest);
		
		preg_match($matchedRoute['GET']->pattern, $this->httpRequest->getPath(), $res);
		array_shift($res);
		
		$this->httpRequest->setParams($res);
		
		$this->invokePluginHook('beforeResponseBuilt');
		
		$this->httpResponse = $this->processPage($matchedRoute);
		
		$this->invokePluginHook('afterResponseBuilt');
				
		return $this->httpResponse;
	}
	
	public function sendResponse() {
		$this->httpResponse = $this->handleRequest();
		
		$this->invokePluginHook('beforeResponseSent');
		
		ob_start();
		ob_implicit_flush(false);
		
		$this->httpResponse->send();
		
		ob_end_flush();
		
		$this->invokePluginHook('afterResponseSent');
		
		exit;
	}
	
	/**
	 * 
	 * @param array $routes
	 * @return \HttpResponse The response of the controller method
     * @throws CoreException
	 */
	public function processPage($routes) {
		$requestSSLRequired = false;
		$controllers = array();
		
		foreach($routes as $r) {
			if($r->sslRequired === true)
				$requestSSLRequired = true;
			
			$className = $r->controllerClass;
			
			if(!isset($controllers[$className])) {
				$controller = new $className($this, $this->httpRequest, $r);
				/*$controller->setCore($this);
				$controller->setHttpRequest($httpRequest);
				$controller->setRoute($r);*/
				
				$controllers[$className] = $controller;
			}
		}
		
		if($requestSSLRequired === true && $this->httpRequest->getProtocol() !== HttpRequest::PROTOCOL_HTTPS)
			RequestHandler::redirect($this->httpRequest->getURL(HttpRequest::PROTOCOL_HTTPS));
		elseif($requestSSLRequired === false && $this->httpRequest->getProtocol() !== HttpRequest::PROTOCOL_HTTP)
			RequestHandler::redirect($this->httpRequest->getURL(HttpRequest::PROTOCOL_HTTP));
		
		//$this->localeHandler->localize($this->httpRequest);
		
		
		/** @var $c FrameworkController */
		$route = ($this->httpRequest->getRequestMethod() === 'POST' && isset($routes['POST']))?$routes['POST']:$routes['GET'];
		
		/** @var HttpResponse $httpResponse */
		$response = call_user_func(array($controllers[$route->controllerClass],$route->controllerMethod));
		
		if(($response instanceof HttpResponse) === false)
			throw new CoreException('Return value of the controller method is not an object of type HttpResponse but of ' . get_class($response));
		
		return $response;
	}
	
	/**
	 * 
	 * @return HttpResponse
	 */
	public function getHttpResponse() {
		return $this->httpResponse;
	}
	
	public function setHttpResponse(HttpResponse $httpResponse) {
		$this->httpResponse = $httpResponse;
	}
	
	/**
	 * 
	 * @return HttpRequest
	 */
	public function getHttpRequest() {
		return $this->httpRequest;
	}
	
	/**
	 * 
	 * @return Settings
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * 
	 * @return RequestHandler
	 */
	public function getRequestHandler() {
		return $this->requestHandler;
	}

    /**
     * @return LocaleHandler
     */
    public function getLocaleHandler() {
		return $this->localeHandler;
	}
	
	public function getSessionHandler() {
		return $this->sessionHandler;
	}

	public function getErrorHandler() {
		return $this->errorHandler;
	}
	
	public function invokePluginHook($hookname) {
		if(is_array($this->plugins) === false)
			return;
		
		foreach($this->plugins as $plugin) {
			if(method_exists($plugin, $hookname) === false)
				continue;
			
			$plugin->$hookname();
		}
	}
}

/* EOF */