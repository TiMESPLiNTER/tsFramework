<?php

namespace ch\timesplinter\controller;

use ch\timesplinter\core\FrameworkLogger;
use ch\timesplinter\core\FrameworkLoggerFactory;
use ch\timesplinter\core\HandleHttpError;
use ch\timesplinter\core\HttpException;
use ch\timesplinter\logger\LoggerFactory;
use ch\timesplinter\core\Core;
use ch\timesplinter\core\HttpRequest;
use ch\timesplinter\core\HttpResponse;
use ch\timesplinter\core\Route;
use ch\timesplinter\logger\TSLogger;
use timesplinter\tsfw\common\StringUtils;

/**
 * Description of StaticPageController
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012 TiMESPLiNTER Webdevelopment
 */
class StaticPageController extends PageController implements HandleHttpError
{
	protected $logger;
	protected $headers;

	/**
	 * @param Core $core The framework core instance
	 * @param HttpRequest $httpRequest The current HTTP request instance
	 * @param Route $route The matched route instance
	 */
	public function __construct(Core $core, HttpRequest $httpRequest, Route $route)
	{
		parent::__construct($core, $httpRequest, $route);

		$this->logger = FrameworkLoggerFactory::getLogger($this);
		$this->headers = array(
			'Content-Type' => 'text/html; charset=UTF-8',
			'Content-Language' => $this->core->getLocaleHandler()->getLanguage()
		);
	}

	/**
	 * Displays a template based page by rendering a template file which is generated by route name plus .html ending
	 * @throws \UnexpectedValueException
	 * @return \HttpResponse The response object with content, headers, HTTP status code, etc.
	 */
	public function getPage()
	{
		$pageData = $this->core->getSettings()->pagedata;
		$routeID = $this->route->id;

		$this->logger->info('Requested route: ' . $routeID);
		$this->logger->debug('Route info', array($this->route));

		if(isset($pageData->$routeID->active))
			$this->view->addActiveHtmlId($pageData->$routeID->active);

		$html = $this->view->render($routeID . '.html', array(
			'siteTitle' => isset($pageData->$routeID->title)?$pageData->$routeID->title:null,
			'runtime' => round(microtime(true) - REQUEST_TIME,3) ,
            'locale' => $this->core->getLocaleHandler()->getLocale(),
            'timezone' => date_default_timezone_get(),
			'base_path' => StringUtils::afterFirst(getcwd(), $_SERVER['DOCUMENT_ROOT'])
		));
		
		return new HttpResponse(200, $html, $this->headers);
	}

	/**
	 * @param HttpException $e The thrown exception
	 * @return HttpResponse The error page response
	 * @throws \ch\timesplinter\core\HttpException
	 */
	public function displayHttpError(HttpException $e)
	{
		try {
			$html = $this->view->render('error.html', array(
				'error' => $e,
				'siteTitle' => 'Error '. $e->getCode(),
				'runtime' => round(microtime(true) - REQUEST_TIME,3) ,
				'locale' => $this->core->getLocaleHandler()->getLocale(),
				'timezone' => date_default_timezone_get(),
				'base_path' => StringUtils::afterFirst(getcwd(), $_SERVER['DOCUMENT_ROOT'])
			));
		} catch(\Exception $e) {
			throw new HttpException($e->getMessage(), 500);
		}

		return new HttpResponse($e->getCode(), $html, $this->headers);
	}
}

/* EOF */