<?php
namespace ch\timesplinter\core;

use ch\timesplinter\common;
use ch\timesplinter\core\Core;

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
	 * @param \HttpRequest $httpRequest
	 * @return void
	 */
	public function localize(HttpRequest $httpRequest) {
		/** @var Domain */
		$currentDomain = DomainUtils::getDomainInfo($this->core->getSettings()->core->domains, $httpRequest->getHost());
		$domainLocale = $currentDomain->localization;
		
		if($domainLocale !== 'browser') {
			setlocale(LC_ALL, $domainLocale);
			$this->locale = $domainLocale;
		} else {
			$locales = self::detectBrowserLocalization($httpRequest->getLanguages());
			
			if(FrameworkUtils::isOS(FrameworkUtils::OS_WINDOWS) === true)
				$locales = self::translateLocales($locales);
			
			foreach($locales as $k => $l) {
				if(setlocale(LC_ALL, $l) !== false) {
					$this->locale = $k;
					return;
				}
			}
		}
		
		$this->timezone = $currentDomain->timezone;
		date_default_timezone_set($currentDomain->timezone);
	}
	
	public function detectBrowserLocalization($acceptedLangs) {
		$langs = array();
		
		foreach($acceptedLangs as $lang => $prio) {
			$localeStrLang = common\StringUtils::beforeFirst($lang, '-');
			$localeStrCountry = common\StringUtils::afterFirst($lang, '-');
			
			$val = $localeStrLang . '_' . strtoupper(($localeStrCountry !== null)?$localeStrCountry:$localeStrLang);
			
			 $langs[$val] = $val;
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
