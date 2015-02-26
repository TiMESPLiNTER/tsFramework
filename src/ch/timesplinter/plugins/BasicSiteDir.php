<?php

namespace ch\timesplinter\plugins;

use ch\timesplinter\common\StringUtils;
use ch\timesplinter\core\FrameworkPlugin;
use ch\timesplinter\core\RequestHandler;
use ch\timesplinter\core\RouteUtils;

/**
 * This plugin takes the first dir in the URI as a language indicator. So an URI like /en/example will be matched
 * against /example and the /en will be taken to set the locale for the site.
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, Pascal Muenst
 * @version 1.0.0
 */
class BasicSiteDir extends FrameworkPlugin
{
	public function afterRequestBuilt()
	{
		$httpRequest = $this->core->getHttpRequest();
		$host = $httpRequest->getHost();

		$pluginSettings = $this->core->getSettings()->{'plugin.basicsitedir'};

		if(isset($pluginSettings->$host) === true) {
			$basicSiteDir = $pluginSettings->$host->basic_site_dir;
		} else {
			$basicSiteDir = $pluginSettings->default->basic_site_dir;
		}

		$httpRequest->setPath(StringUtils::afterFirst($httpRequest->getPath(), $basicSiteDir));
	}
}

/* EOF */