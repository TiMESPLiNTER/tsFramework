<?php

/**
 * Description of ManifestParser
 *
 * @author pascal91
 */
class ManifestParser {
	private $xml;
	
	public function __construct($manifestFilepath) {
		$this->xml = simplexml_load_file($manifestFilepath);
	}
	
	public function parseSites() {
		$sites = array();
		
		
		foreach($this->xml->sites->site as $s) {
			$sAttr = $s->attributes();
			
			$path = SimpleXMLUtils::getString($sAttr['path']);
			$sslRequired = SimpleXMLUtils::getBoolean($sAttr['sslrequired']);
			$controller = SimpleXMLUtils::getString($sAttr['controller']);
			
			$sites[$path] = new Site($path, $controller, $sslRequired);
		}
		
		return $sites;
	}
	
	public function parseDomains() {
		$domains = array();
		
		foreach($this->xml->domains->domain as $domain) {
			$dAttr = $domain->attributes();
			
			$name = SimpleXMLUtils::getString($dAttr['name']);
			$locale = SimpleXMLUtils::getString($dAttr['locale']);
			$environment = SimpleXMLUtils::getString($dAttr['environment']);
			$startPage = SimpleXMLUtils::getString($dAttr['startpage']);
			$template = SimpleXMLUtils::getString($dAttr['template']);
			
			$domains[$name] = new Domain($name, $locale, $environment, $startPage, $template);
		}
		
		return $domains;
	}
}

?>
