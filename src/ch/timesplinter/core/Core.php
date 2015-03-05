<?php

namespace ch\timesplinter\core;

use timesplinter\tsfw\common\StringUtils;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright 2012, TiMESPLiNTER Webdevelopment
 */
class Core
{
	const CACHE_DIR = 'cache';

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
	private $pluginManager;

	/** @var HttpRequest */
	private $httpRequest;
	/** @var HttpResponse */
	private $httpResponse;
	/** @var Route */
	private $route;

	private $environment;
	private $currentDomain;
	private $fwRoot;
	private $siteRoot;
	private $siteCacheDir;

	public function __construct($fwRoot, $siteRoot)
	{
		$this->fwRoot = $fwRoot;
		$this->siteRoot = $siteRoot;
		$this->siteCacheDir = $siteRoot . 'cache' . DIRECTORY_SEPARATOR;

		$this->httpRequest = $this->createHttpRequest();

		$this->settings = new Settings(
			$siteRoot . 'settings' . DIRECTORY_SEPARATOR,
			$this->siteCacheDir,
			array(
				'fw_dir' => $fwRoot,
				'site_root' => $siteRoot,
				'domain' => $this->httpRequest->getHost()
			)
		);

		/*
		 * TODO: if we request a file that doesn't exist from a domain that is not registered in settings of the fw
		 * we can't do that so
		 */

		$this->environment = $this->getEnvironmentFromRequest($this->httpRequest);
		$this->currentDomain = isset($this->settings->core->domains->{$this->httpRequest->getHost()})
			?$this->settings->core->domains->{$this->httpRequest->getHost()}:$this->settings->defaults->domain;

		if($this->environment === null)
			RequestHandler::redirect($this->settings->defaults->domain);

		FrameworkLoggerFactory::setDefaults($this->environment, $fwRoot, $siteRoot);

        $this->errorHandler = new ErrorHandler($this);
        $this->errorHandler->register();


		$this->localeHandler = new LocaleHandler($this);
		$this->sessionHandler = new SessionHandler($this);
		$this->logger = FrameworkLoggerFactory::getLogger($this);
		
		$this->pluginManager = new PluginManager($this);
		$this->pluginManager->loadPlugins($this->settings->core->plugins);
	}

	/**
	 * Creates a HttpRequest object for the current request
	 * @return HttpRequest
	 */
	protected function createHttpRequest()
	{
		$protocol = (isset($_SERVER['HTTPS']) === true && $_SERVER['HTTPS'] === 'on') ? HttpRequest::PROTOCOL_HTTPS : HttpRequest::PROTOCOL_HTTP;
		$uri = StringUtils::startsWith($_SERVER['REQUEST_URI'], '/index.php')?StringUtils::afterFirst($_SERVER['REQUEST_URI'], '/index.php'):$_SERVER['REQUEST_URI'];
		
		$subFolder = StringUtils::afterFirst(getcwd(), $_SERVER['DOCUMENT_ROOT']);
		$cleanPath = StringUtils::between($uri, $subFolder, '?');

		$languages = array();
		$langsRates = explode(',', isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null);

		foreach($langsRates as $lr) {
			$lrParts = explode(';', $lr);

			$languages[$lrParts[0]] = isset($lrParts[1])?(float)StringUtils::afterFirst($lrParts[1], 'q='):1.0;
		}

		$requestTime = new \DateTime();
		$requestTime->setTimestamp($_SERVER['REQUEST_TIME']);

		$httpRequest = new HttpRequest();

		$httpRequest->setHost($_SERVER['SERVER_NAME']);
		$httpRequest->setPath($cleanPath);
		$httpRequest->setPort($_SERVER['SERVER_PORT']);
		$httpRequest->setProtocol($protocol);
		$httpRequest->setQuery($_SERVER['QUERY_STRING']);
		$httpRequest->setURI($uri);

		$httpRequest->setRequestTime($requestTime);
		$httpRequest->setRequestMethod($_SERVER['REQUEST_METHOD']);
		$httpRequest->setUserAgent(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null);
		$httpRequest->setLanguages($languages);
		$httpRequest->setAcceptLanguage(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:null);
		$httpRequest->setRemoteAddress($_SERVER['REMOTE_ADDR']);
		$httpRequest->setReferrer(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);

		return $httpRequest;
	}

	public function handleRequest()
	{
		$this->sessionHandler->start();

		$this->pluginManager->invokePluginHook('beforeRequestBuilt');

		$this->httpRequest = $this->createHttpRequest();

        $this->pluginManager->invokePluginHook('afterRequestBuilt');

        $this->localeHandler->localize($this->httpRequest);

		if($this->httpRequest->getPath() === '/') {
			// Load the default page as "virtual" httpRequest for the base url (no uri)
			$routeId = (string)$this->currentDomain->startroute;

			if($this->httpRequest->getPath() !== $routeId)
				RequestHandler::redirect($routeId);
		}

		$matchedRoutes = RouteUtils::matchRoutesAgainstPath($this->settings->core->routes, $this->httpRequest);

		if($matchedRoutes === null)
			throw new HttpException('No route did match for: ' . $this->httpRequest->getPath(), 404);

		$this->pluginManager->invokePluginHook('beforeResponseBuilt');

		$this->httpResponse = $this->processPage($matchedRoutes);

		$this->pluginManager->invokePluginHook('afterResponseBuilt');

		return $this->httpResponse;
	}

	public function sendResponse()
	{
		$this->httpResponse = $this->handleRequest();

		$this->pluginManager->invokePluginHook('beforeResponseSent');

		if($this->httpResponse->isStream() === false) {
			ob_start();
			ob_implicit_flush(false);
		}

		$this->httpResponse->send();
		
		if($this->httpResponse->isStream() === false)
			ob_end_flush();

		$this->pluginManager->invokePluginHook('afterResponseSent');

		exit;
	}

	/**
	 *
	 * @param array $routes
	 * @throws CoreException
	 * @throws HttpException|\Exception
	 * @return \HttpResponse The response of the controller method
	 */
	public function processPage($routes)
	{
		$httpRequestMethod = $this->httpRequest->getRequestMethod();

		$this->route = RouteUtils::getFirstRouteWhichHasMethod($routes, $httpRequestMethod);

		if($this->route === null)
			throw new HttpException('Method ' . $httpRequestMethod . ' is not allowed for this path. Use ' . implode(', ', array_keys($routes)) . ' instead', 405);

		if($this->route->sslRequired === true && $this->httpRequest->getProtocol() !== HttpRequest::PROTOCOL_HTTPS)
			RequestHandler::redirect($this->httpRequest->getURL(HttpRequest::PROTOCOL_HTTPS));
		elseif($this->route->sslForbidden === true && $this->httpRequest->getProtocol() !== HttpRequest::PROTOCOL_HTTP)
			RequestHandler::redirect($this->httpRequest->getURL(HttpRequest::PROTOCOL_HTTP));

		$response = null;
		$routeMethod = $this->route->methods[$httpRequestMethod];

		if(class_exists($routeMethod->controllerClass) === false)
			throw new CoreException('Could not find class: ' . $routeMethod->controllerClass);

		$controllerInstance = new $routeMethod->controllerClass($this, $this->httpRequest, $this->route);
		$responseCallback = array($controllerInstance, $routeMethod->controllerMethod);

		if(is_callable($responseCallback, false) === false)
			throw new CoreException('Could not call: ' . $routeMethod->controllerClass . '->' . $routeMethod->controllerMethod . '. This is no valid callback! Maybe you attempt to call a static method or you propably misspelled the "controller:method" name');

		try {
			$response = call_user_func($responseCallback);
		} catch(HttpException $e) {
			if(($controllerInstance instanceof HandleHttpError) === false)
				throw $e;

			/** @var HttpResponse $response */
			$response = $controllerInstance->displayHttpError($e);
			$response->setHttpResponseCode($e->getCode());
		}

		if(($response instanceof HttpResponse) === false)
			throw new CoreException('Return value of the controller method "' . $routeMethod->controllerClass . '->' . $routeMethod->controllerMethod . '" is not an object of type HttpResponse but of ' . (is_object($response)?get_class($response):'a php native type'));

		return $response;
	}

	protected function getEnvironmentFromRequest(HttpRequest $httpRequest)
	{
		if(($di = DomainUtils::getDomainInfo($this->settings->core->domains, $httpRequest->getHost())) === null) {
			return isset($this->settings->defaults->environment)?$this->settings->defaults->environment:null;
		}

		return $di->environment;
	}

	/**
	 *
	 * @return HttpResponse
	 */
	public function getHttpResponse()
	{
		return $this->httpResponse;
	}

	public function setHttpResponse(HttpResponse $httpResponse)
	{
		$this->httpResponse = $httpResponse;
	}

	/**
	 *
	 * @return HttpRequest
	 */
	public function getHttpRequest()
	{
		return $this->httpRequest;
	}

	/**
	 * @return \ch\timesplinter\core\Route
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 *
	 * @return Settings
	 */
	public function getSettings()
	{
		return $this->settings;
	}

	/**
	 * Returns the current domain in which the framework is operating
	 * @return \stdClass|null The current domain or null if no domain matched
	 */
	public function getCurrentDomain()
	{
		return $this->currentDomain;
	}

	/**
	 *
	 * @return RequestHandler
	 */
	public function getRequestHandler()
	{
		return $this->requestHandler;
	}

    /**
     * @return LocaleHandler
     */
    public function getLocaleHandler()
    {
		return $this->localeHandler;
	}

	public function getSessionHandler()
	{
		return $this->sessionHandler;
	}

	public function getErrorHandler()
	{
		return $this->errorHandler;
	}

	public function getPluginManager()
	{
		return $this->pluginManager;
	}

	/**
	 * Returnts the path to the root directory of the framework
	 * @return string The framework root path
	 */
	public function getFwRoot()
	{
		return $this->fwRoot;
	}

	/**
	 * Returns the path to the root directory of the site
	 * @return string The site root path
	 */
	public function getSiteRoot()
	{
		return $this->siteRoot;
	}

	public function getSiteCacheDir()
	{
		return $this->siteCacheDir;
	}
}

/* EOF */