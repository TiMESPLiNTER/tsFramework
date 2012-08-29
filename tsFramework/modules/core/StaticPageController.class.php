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
		$pagesDir = SITE_ROOT .  FW_DIR . 'templates/' . $this->requestedTemplate . '/pages/';
		
		$this->tplEngine->addData('pagesDir', $this->getPageTplFile($pagesDir, $this->requestedPage));
		$this->tplEngine->addData('pageHandler', $this);
		
		$this->tplEngine->addData('siteTitle', 'Start Page');
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