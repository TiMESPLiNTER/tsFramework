<?php

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2013, METANET AG
 * @version 1.0.0
 */

namespace ch\timesplinter\core;


use ch\timesplinter\controller\MyErrorController;
use ch\timesplinter\template\TemplateCache;
use ch\timesplinter\template\TemplateEngine;

class HttpException extends FrameworkException {
	public function handleException(Core $core, HttpRequest $httpRequest) {
		$tplDir = $core->getSiteRoot() . 'templates' . DIRECTORY_SEPARATOR . $core->getCurrentDomain()->template . DIRECTORY_SEPARATOR;
		$templateFile = $tplDir . 'template.html';
		$tplFilePath = $tplDir . 'pages' . DIRECTORY_SEPARATOR . 'error.html';

		$cacheDir = $core->getSiteRoot() . 'cache' . DIRECTORY_SEPARATOR  . 'pages' . DIRECTORY_SEPARATOR . $core->getCurrentDomain()->template . DIRECTORY_SEPARATOR;
		$tplCache = new TemplateCache($cacheDir, 'cache.template');
		$tplEngine = new TemplateEngine($tplCache, 'tst');

		$tplVars = array(
			'this' => $tplFilePath,
			'siteTitle' => 'Error ' . $this->getCode(),
			'e' => $this
		);

		return $tplEngine->getResultAsHtml($templateFile, $tplVars);
	}
}

/* EOF */