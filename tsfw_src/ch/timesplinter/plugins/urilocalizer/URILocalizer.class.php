<?php

namespace ch\timesplinter\plugins\urilocalizer;

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
class URILocalizer extends FrameworkPlugin {
    public function afterRequestBuilt() {
        $httpRequest = $this->core->getHttpRequest();

        $host = $httpRequest->getHost();

        if(!isset($this->core->getSettings()->core->domains->$host->localization) || $this->core->getSettings()->core->domains->$host->localization !== 'uri')
            return;

        $this->core->getSettings()->core->domains->$host->localization = 'browser';

        $uriLocale = StringUtils::beforeFirst(substr($httpRequest->getURI(),1), '/');
        $newURI = '/' . StringUtils::afterFirst(substr($httpRequest->getURI(),1), '/');

        /* dont do 3 regex's for nothig */
        if(!preg_match('/^[a-z]{2}$/', $uriLocale)) {
            if(preg_match('/^[A-Z]{2}$/', $uriLocale)) {
                RequestHandler::redirect('/' . strtolower($uriLocale) . $newURI);
            } elseif(preg_match('/^([a-z]{2})-([a-z]{2})$/', $uriLocale, $match)) {
                $uriLocale = $match[1] . '_' . strtoupper($match[2]);
            } elseif(preg_match('/^([A-Za-z]{2})_([A-Za-z]{2})$/', $uriLocale, $match)) {
                RequestHandler::redirect('/' . strtolower($match[1]) . '-' . strtolower($match[2]) . $newURI);
            }
        }

        $localesAvailable = $this->core->getSettings()->locales;

        if(isset($localesAvailable->$uriLocale)) {
            $httpRequest->setLanguages(array($uriLocale => 1.0));
            $httpRequest->setURI($newURI);

            $newPath = '/' . StringUtils::afterFirst(substr($httpRequest->getPath(),1), '/');
            $httpRequest->setPath($newPath);

            return;
        }

        $resRoutes = RouteUtils::matchRoutesAgainstPath($this->core->getSettings()->core->routes, $newURI);
        $defaultLocale = $this->core->getSettings()->{'plugin.urilocalization'}->default_uri_locale;

        if(isset($resRoutes['GET']) && count($resRoutes['GET']) > 0) {
            RequestHandler::redirect('/' . $defaultLocale . $newURI);
        }

        RequestHandler::redirect('/' . $defaultLocale . $httpRequest->getURI());
    }
}

/* EOF */