<?php

/**
 * Description of Core
 *
 * @author pascal91
 */
class Core {
	/** @var Settings */
	private $settings;
	/** @var RequestHandler */
	private $requestHandler;
	/** @var LocaleHandler */
	private $localeHandler;
	
	public function __construct($requestHandler) {
		$this->requestHandler = $requestHandler;
		
		$this->settings = new Settings();
		$this->localeHandler = new LocaleHandler($this);
	}
	
	//put your code here
	public function loadManifest() {
		$manifestParser = new ManifestParser(siteRoot . fwDir . 'settings/manifest.xml');
		
		$this->settings->setValue('tsfw_sites', $manifestParser->parseSites());
		$this->settings->setValue('tsfw_domains', $manifestParser->parseDomains());
		
		//echo '<pre>'; var_dump($this->settings->getValue('tsfw_sites'),$this->settings->getValue('tsfw_domains')); echo'</pre>';
	}
	
	/**
	 * 
	 * @param Site $site
	 */
	public function processPage($site) {
		$this->localeHandler->localize();
		
		$c = $site->getController();
		$controller = new $c;
		$controller->setCore($this);
		
		$controller->generate();
		$controller->show();
		
	}
	
	/**
	 * 
	 * @return Settings
	 */
	public function getSettings() {
		return $this->settings;
	}
	
	/**
	 * 
	 * @return RequestHandler
	 */
	public function getRequestHandler() {
		return $this->requestHandler;
	}
}

?>
