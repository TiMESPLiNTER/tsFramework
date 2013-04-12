<?php

namespace ch\timesplinter\plugins\browsercaching;

use ch\timesplinter\core\FrameworkPlugin;
use ch\timesplinter\core\HttpResponse;

/**
 * Description of ETag
 *
 * @author pascal91
 */
class BrowserCaching extends FrameworkPlugin {
	/** @Override */
	public function afterResponseBuilt() {
		$pluginSettings = $this->core->getSettings()->{'plugin.browsercaching'};
		$httpResponse = $this->core->getHttpResponse();
		
		$contentHash = md5($httpResponse->getContent());
		
		// ETag only if response is ok -> code 200
		if($httpResponse->getHttpStatusCode() !== 200)
			return;
		
		if(isset($_SERVER['HTTP_IF_NONE_MATCH']) === true && $_SERVER['HTTP_IF_NONE_MATCH'] === $contentHash) {
			$httpResponse = new HttpResponse(304, null, array());
		} else {
			$httpResponse->addHeader('Etag', $contentHash);
		}
		
		//make sure caching is turned on
		$httpResponse->addHeader('Cache-Control','private, max-age=' . $pluginSettings->default_max_age . ', must-revalidate');
		$httpResponse->addHeader('Pragma','private');
		$httpResponse->addHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $pluginSettings->default_max_age));
		
		$this->core->setHttpResponse($httpResponse);
	}
}

/* EOF */