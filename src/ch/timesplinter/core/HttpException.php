<?php

namespace ch\timesplinter\core;

use timesplinter\tsfw\template\DirectoryTemplateCache;
use timesplinter\tsfw\template\TemplateEngine;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 */

class HttpException extends FrameworkException
{
	public function handleException(Core $core, HttpRequest $httpRequest)
	{
		$tplDir = $core->getSiteRoot() . 'templates' . DIRECTORY_SEPARATOR . $core->getCurrentDomain()->template . DIRECTORY_SEPARATOR;
		$templateFile = $tplDir . 'template.html';
		$tplFilePath = $tplDir . 'pages' . DIRECTORY_SEPARATOR . 'error.html';

		$cacheDir = $core->getSiteRoot() . 'cache' . DIRECTORY_SEPARATOR  . 'pages' . DIRECTORY_SEPARATOR . $core->getCurrentDomain()->template . DIRECTORY_SEPARATOR;
		$tplCache = new DirectoryTemplateCache($cacheDir, 'cache.template');
		$tplEngine = new TemplateEngine($tplCache, 'tst');

		return $tplEngine->getResultAsHtml($templateFile, array(
			'this' => $tplFilePath,
			'siteTitle' => 'Error ' . $this->getCode(),
			'e' => $this
		));
	}
}

/* EOF */