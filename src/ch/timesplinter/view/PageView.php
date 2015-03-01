<?php

namespace ch\timesplinter\view;

use ch\timesplinter\controller\PageController;
use timesplinter\tsfw\template\DirectoryTemplateCache;
use timesplinter\tsfw\template\TemplateEngine;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2014, TiMESPLiNTER Webdevelopment
 */
class PageView extends View
{
	protected $activeHtmlIds;
	
	public function __construct(PageController $controller)
	{
		parent::__construct($controller);
		
		$this->activeHtmlIds = array();
		$cacheDir = $this->controller->getCore()->getSiteRoot() . 'cache' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $this->controller->getCurrentDomain()->template . DIRECTORY_SEPARATOR;
		$tplCache = new DirectoryTemplateCache($cacheDir, 'cache.template');
		$this->tplEngine = new TemplateEngine($tplCache, 'tst');
	}
	
	public function render($tplFile, array $tplVars)
	{
		$tplDir = $this->controller->getCore()->getSiteRoot() . 'templates' . DIRECTORY_SEPARATOR . $this->controller->getCurrentDomain()->template . DIRECTORY_SEPARATOR;
		$templateFile = $tplDir . 'template.html';
		$tplFilePath = 'pages' . DIRECTORY_SEPARATOR . $tplFile;
		$tplVars['this'] = $tplFilePath;
		$tplVars['_site'] = ($this->controller->getRoute() !== null)?(string)$this->controller->getRoute()->id:null;
		
		return preg_replace_callback('/\s+id="nav-(.+?)"/', array($this, 'setCSSActive'), $this->tplEngine->getResultAsHtml($templateFile, $tplVars));
	}
	
	protected function setCSSActive($m)
	{
		return $m[0] . ($this->activeHtmlIds !== null && in_array($m[1], $this->activeHtmlIds)?' class="active"':null);
	}
	
	public function setActiveHtmlIds(array $activeHtmlIds)
	{
		$this->activeHtmlIds = $activeHtmlIds;
	}
	
	public function addActiveHtmlId($activeHtmlId)
	{
		$this->activeHtmlIds[] = $activeHtmlId;
	}
}
/* EOF */