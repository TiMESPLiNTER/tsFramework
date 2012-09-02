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
		$domains = $this->core->getSettings()->getValue('domains','core');
		/** @var Domain */
		$currentDomain = $domains[$this->core->getRequestHandler()->getRequestDomain()];
		
		setlocale(LC_ALL, $currentDomain->locale);
		date_default_timezone_set($currentDomain->timezone);

	}
}

?>
