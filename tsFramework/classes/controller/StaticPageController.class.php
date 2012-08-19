<?php

/**
 * Description of StaticPageController
 *
 * @author pascal91
 */
class StaticPageController extends PageController {
	private $reqFilePath;
	
	public function generate() {
		$reqArr = $this->core->getRequestHandler()->getRequestArray();
		$this->reqFilePath = siteRoot . 'resources/pages/' . $reqArr['fileName'] . '.html';
	}
	
	public function show() {
		// open the file in a binary mode
		$fp = fopen( $this->reqFilePath , 'rb');
		
		// dump the html
		fpassthru($fp);
	}
}

?>