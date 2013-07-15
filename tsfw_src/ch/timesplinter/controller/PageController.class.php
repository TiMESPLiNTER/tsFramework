<?php
namespace ch\timesplinter\controller;

use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TemplateCache;
use ch\timesplinter\core\Core;
use ch\timesplinter\core\HttpRequest;
use ch\timesplinter\core\Route;
use ch\timesplinter\core\HttpResponse;

/**
 * The standard PageController loads the defined template for the current
 * domain and print it out
 * 
 * @author Pascal Münst
 * @copyright (c) 2012, Pascal Münst
 * @version 1.0
 */
abstract class PageController extends FrameworkController {
	/** @var TemplateEngine */
	protected $tplEngine;
	protected $activeHtmlIds;
	
	public function __construct(Core $core, HttpRequest $httpRequest, Route $route) {
		parent::__construct($core, $httpRequest, $route);
		
		$this->activeHtmlIds = array();
	}
	
	/**
	 * Parses a template file
	 * @param string $tplFile
	 * @param array $vars
	 * @return string The parsed template
	 */	
	public function render($tplFile, $vars = array()) {
		//$currentDomain = DomainUtils::getDomainInfo($this->core->getSettings()->core->domains, $this->httpRequest->getHost());
		//var_dump($this->core->getSettings()->getAllValues()); exit;
		$tplDir = SITE_ROOT . 'templates' . DIRECTORY_SEPARATOR . $this->currentDomain->template . DIRECTORY_SEPARATOR;
		$templateFile = $tplDir . 'template.html';
		$tplFilePath = $tplDir . 'pages' . DIRECTORY_SEPARATOR . $tplFile . '.html';
		
		$cacheDir = CACHE_DIR . 'pages' . DIRECTORY_SEPARATOR . $this->currentDomain->template . DIRECTORY_SEPARATOR;
		$tplCache = new TemplateCache($cacheDir, 'cache.template');
		$this->tplEngine = new TemplateEngine($tplCache, 'tst');

		$this->tplEngine->setAllData($vars);
		$vars['this'] = $tplFilePath;
		$vars['_site'] = ($this->route !== null)?(string)$this->route->id:null;
		
		return preg_replace_callback('/\s+id="nav-(.+?)"/', array($this,'setCSSActive'), $this->tplEngine->getResultAsHtml($templateFile, $vars));
	}
	
	protected function generateHttpResponse($httpStatusCode = 200, $html = null, $headers = array()) {
		$headers['Content-Type'] = 'text/html; charset=UTF-8';
        $headers['Content-Language'] = substr($this->core->getLocaleHandler()->getLocale(),0,2);
		
		return new HttpResponse($httpStatusCode, $html, $headers);
	}
	
	private function setCSSActive($m) {
		return $m[0] . ($this->activeHtmlIds !== null && in_array($m[1], $this->activeHtmlIds)?' class="active"':null);
	}
}

/* EOF */