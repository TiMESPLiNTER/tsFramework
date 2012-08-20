<?php

/**
 * Description of StaticPageController
 *
 * @author pascal91
 */
class StaticPageController extends PageController {
	private  $logger;
	
	public function __construct() {
		$this->logger = LoggerFactory::getLoggerByName('dev', __CLASS__);
	}


	public function generate() {
		$domains = $this->core->getSettings()->getValue('tsfw_domains');
		/** @var Domain */
		$currentDomain = $domains[$this->core->getRequestHandler()->getRequestDomain()];
		$reqArr = $this->core->getRequestHandler()->getRequestArray();
		
		$tplDir = siteRoot .  'resources/templates/' . $currentDomain->getTemplate() . '/';
		$pagesDir = $tplDir . 'pages/';
		$templateFile = $tplDir . 'template.html';
		
		
		$cacheDir = siteRoot . fwDir . 'cache/pages/' . $currentDomain->getTemplate() . '/';
		$tplCache = new TemplateCache($cacheDir, 'cache.template');
		$this->tplEngine = new TemplateEngine($tplCache, $templateFile, 'tst');
		$this->tplEngine->addData('pagesDir', $this->getPageTplFile($pagesDir, $reqArr['fileName']));
		$this->tplEngine->addData('pageHandler', $this);
	}
	
	public function show() {
		$this->tplEngine->parse();
		print $this->tplEngine->getResultAsHtml();
	}
	
	private function getPageTplFile($pagesDir, $fileTitle) {
		$contentFile = $pagesDir . $fileTitle . '.html';

		if(file_exists($contentFile) === false) {
			$this->logger->error('File does not exists: ' . $contentFile);
			$errorHandler = new ErrorHandler();
			$errorHandler->displayHttpError(404);
		}

		return $contentFile;
	}
}

?>