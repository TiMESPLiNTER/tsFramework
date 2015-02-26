<?php

namespace ch\timesplinter\core;

use ch\timesplinter\common\StringUtils;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) TiMESPLiNTER Webdevelopment
 */
class LocaleHandler
{
	/** @var Core $core */
	private $core;
	private $locale;
	private $timezone;

	private $currentTextdomain;
	
	public function __construct(Core $core)
	{
		$this->core = $core;
		$this->locale = null;

		$this->currentTextdomain = null;
	}

	/**
	 *
	 * @param HttpRequest $httpRequest
	 */
	public function localize(HttpRequest $httpRequest)
	{
		/** @var Domain */
		$domainLocale = $this->core->getCurrentDomain()->localization;

		$locales = ($domainLocale === 'browser')?$this->detectBrowserLocalization($httpRequest->getLanguages()):array($domainLocale => 1.0);

		// Try to set browser locale or fix domain locale
		$settedLocale = $this->setLocale(array_keys($locales));
		
		// Try to set fallback locale if browser or fix locale not worked
		$this->locale = ($settedLocale !== false)?$settedLocale:$this->setLocale(array($this->core->getSettings()->defaults->localization));


		$this->timezone = isset($currentDomain)?$currentDomain->timezone:$this->core->getSettings()->defaults->timezone;
		date_default_timezone_set($this->timezone);
	}

	private function setLocale($locales)
	{
		foreach($locales as $l) {
			if(!isset($this->core->getSettings()->locales->$l))
				continue;

			if(setlocale(LC_ALL, $this->core->getSettings()->locales->$l->names) === false)
				continue;

			return $l;
		}

		return false;
	}
	
	public function detectBrowserLocalization($acceptedLangs)
	{
		$langs = array();

		foreach($acceptedLangs as $lang => $prio) {
			$localeStrLang = StringUtils::beforeFirst($lang, '-');
			$localeStrCountry = StringUtils::afterFirst($lang, '-');

			$val = $localeStrLang . (($localeStrCountry !== null)? '_' . strtoupper($localeStrCountry):null);
			
			 $langs[$val] = $prio;
		}
		
		return $langs;
	}

	public function bindTextdomain($textdomain, $location) {
		bindtextdomain($textdomain, $location);
	}

	public function gettext($message, $textdomain = null)
	{
		if($textdomain !== null && $this->currentTextdomain !== $textdomain)
			$this->currentTextdomain = textdomain($textdomain);

		return gettext($message);
	}

	public function ngettext($msgid1, $msgid2, $n, $textdomain = null)
	{
		if($textdomain !== null && $this->currentTextdomain !== $textdomain)
			$this->currentTextdomain = textdomain($textdomain);

		return ngettext($msgid1, $msgid2, $n);
	}

	public function getLocale()
	{
		return $this->locale;
	}
	
	public function getCountry()
	{
		return substr($this->locale, 3);
	}
	
	public function getLanguage()
	{
		return substr($this->locale, 0, 2);
	}

	public function getDateTimeFormat()
	{
		return $this->core->getSettings()->locales->{$this->locale}->datetime_format;
	}

	public function getDateFormat()
	{
		return $this->core->getSettings()->locales->{$this->locale}->date_format;
	}

	public function getTimeFormat()
	{
		return $this->core->getSettings()->locales->{$this->locale}->time_format;
	}
}

/* EOF */