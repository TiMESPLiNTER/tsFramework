<?php

/**
 * The standard PageController loads the defined template for the current
 * domain and print it out
 * 
 * @author Pascal Münst
 * @copyright (c) 2012, Pascal Münst
 * @version 1.0
 */
abstract class PageController {
	/** @var Core */
	protected $core;
	/** @var TemplateEngine */
	protected $tplEngine;
	
	protected $requestedPage;
	protected $requestedTemplate;

	public function prepare($core) {
		$this->core = $core;
		
		$domains = $this->core->getSettings()->getValue('tsfw_domains');
		/** @var Domain */
		$currentDomain = $domains[$this->core->getRequestHandler()->getRequestDomain()];
		$reqArr = $this->core->getRequestHandler()->getRequestArray();
		
		$this->requestedPage = $reqArr['fileName'];
		$this->requestedTemplate =  $currentDomain->getTemplate();
		
		$tplDir = SITE_ROOT .  'resources/templates/' . $this->requestedTemplate . '/';
		$templateFile = $tplDir . 'template.html';
		
		
		$cacheDir = SITE_ROOT . FW_DIR . 'cache/pages/' . $this->requestedTemplate . '/';
		$tplCache = new TemplateCache($cacheDir, 'cache.template');
		$this->tplEngine = new TemplateEngine($tplCache, $templateFile, 'tst');
	}

	abstract public function generate();
	
	public function show() {
		$this->tplEngine->parse();
		$this->tplEngine->addData('runtime', round(microtime(true)-REQUEST_TIME,3));
		print $this->tplEngine->getResultAsHtml();
	}
}

?>