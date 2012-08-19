<?php

/**
 * Description of Domain
 *
 * @author pascal91
 */
class Domain {
	private $name;
	private $locale;
	private $environment;
	private $startPage;
	
	public function __construct($name, $locale, $environment, $startPage) {
		$this->name = $name;
		$this->locale = $locale;
		$this->environment = $environment;
		$this->startPage = $startPage;
	}

	public function getLocale() {
		return $this->locale;
	}
	
	public function getTimezone() {
		return 'Europe/Zurich';
	}
	
	public function getStartPage() {
		return $this->startPage;
	}
}

?>
