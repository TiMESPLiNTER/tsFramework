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
	private $template;
	
	public function __construct($name, $locale, $environment, $startPage, $template) {
		$this->name = $name;
		$this->locale = $locale;
		$this->environment = $environment;
		$this->startPage = $startPage;
		$this->template = $template;
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
	
	public function getTemplate() {
		return $this->template;
	}
}

?>
