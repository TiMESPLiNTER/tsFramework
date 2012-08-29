<?php

/**
 * Description of LocaleHandler
 *
 * @author pascal91
 */
class LocaleHandler {
	/** @var Core */
	private $core;
	
	public function __construct($core) {
		$this->core = $core;
	}
	
	public function localize() {
		$domains = $this->core->getSettings()->getValue('tsfw_domains');
		/** @var Domain */
		$currentDomain = $domains[$this->core->getRequestHandler()->getRequestDomain()];
		
		setlocale(LC_ALL, $currentDomain->getLocale());
		date_default_timezone_set($currentDomain->getTimezone());
		
		//var_dump(setlocale(LC_ALL, 0), date_default_timezone_get());
	}
}

?>
