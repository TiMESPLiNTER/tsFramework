<?php
namespace ch\timesplinter\core;

use ch\timesplinter\common\StringUtils;
use ch\timesplinter\core\Core;
use ch\timesplinter\core\HttpRequest;

/**
 * Description of LocaleHandler
 *
 * @author pascal91
 */
class LocaleHandler {
	/** @var Core $core */
	private $core;
	private $locale;
	private $timezone;
	
	public function __construct(Core $core) {
		$this->core = $core;
		$this->locale = null;
	}

	/**
	 *
	 * @param HttpRequest $httpRequest
	 */
	public function localize(HttpRequest $httpRequest) {
		/** @var Domain */
		$localesAvailable = $this->core->getSettings()->locales;
		$currentDomain = DomainUtils::getDomainInfo($this->core->getSettings()->core->domains, $httpRequest->getHost());

		//var_dump($currentDomain, $localesAvailable);

		$domainLocale = ($currentDomain !== null)?$currentDomain->localization:$this->core->getSettings()->defaults->localization;

		$locales = ($domainLocale === 'browser')?$this->detectBrowserLocalization($httpRequest->getLanguages()):array($domainLocale => 1.0);

		// Try to set browser locale or fix domain locale
		$settedLocale = $this->setLocale(array_keys($locales));

		// Try to set fallback locale if browser or fix locale not worked
		$this->locale = ($settedLocale !== false)?$settedLocale:$this->setLocale(array($this->core->getSettings()->defaults->localization));


		$this->timezone = isset($currentDomain)?$currentDomain->timezone:$this->core->getSettings()->defaults->timezone;
		date_default_timezone_set($this->timezone);
	}

	private function setLocale($locales) {
		foreach($locales as $l) {
			if(!isset($this->core->getSettings()->locales->$l))
				continue;

			$localeNames = $this->core->getSettings()->locales->$l->names;

			foreach($localeNames as $ln) {
				if(setlocale(LC_ALL, $ln) !== false) {
					return $ln;
				}
			}
		}

		return false;
	}
	
	public function detectBrowserLocalization($acceptedLangs) {
		$langs = array();

		foreach($acceptedLangs as $lang => $prio) {
			$localeStrLang = StringUtils::beforeFirst($lang, '-');
			$localeStrCountry = StringUtils::afterFirst($lang, '-');

			$val = $localeStrLang . (($localeStrCountry !== null)? '_' . strtoupper($localeStrCountry):null);
			
			 $langs[$val] = $prio;
		}
		
		return $langs;
	}
	
	private function translateLocales($locales) {
		$localesWin = array(
			 'de_DE' => 'german'
			,'de_CH' => 'german-swiss'
			,'de_AU' => 'german-austrian'
			,'fr_FR' => 'french'
			,'fr_CH' => 'french-swiss'
			,'fr_BE' => 'french-belgian'
			,'fr_CA' => 'french-canadian'
			,'it_IT' => 'italian'
			,'it_CH' => 'italian-swiss'
			,'en_EN' => 'english-uk'
			,'en_US' => 'english-american'
			,'en_CA' => 'english-canadian'
		);
		
		if(is_array($locales) === true) {
			$localesWinTmp = array();
			
			foreach($locales as $l)
				$localesWinTmp[$l] = isset($localesWin[$l])?$localesWin[$l]:$l;
			
			return $localesWinTmp;
		} else {
			if(isset($localesWin[$locales]))
				return $localesWin[$locales];
		}
		
		return $locales;
	}
	
	public function getLocale() {
		return $this->locale;
	}
	
	public function getCountry() {
		return substr($this->locale, 3);
	}
	
	public function getLanguage() {
		return substr($this->locale, 0, 2);
	}
}

?>
