<?php
namespace ch\timesplinter\core;

use \DateTime;

/**
 * Description of HttpRequest
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 */
class HttpRequest {
	const PROTOCOL_HTTP = 'http';
	const PROTOCOL_HTTPS = 'https';

	const VAR_GET = '_GET';
	const VAR_POST = '_POST';
	const VAR_FILE = '_FILE';

	private $protocol;
	private $host;
	private $port;
	private $path;
	private $query;
	private $uri;
		
	private $params;
	private $requestMethod;
	private $requestTime;
	private $languages;
	private $userAgent;
	
	public function __construct() {
		$this->params = array();
	}
	
	public function setProtocol($protocol) {
		$this->protocol = $protocol;
	}
	
	public function getProtocol() {
		return $this->protocol;
	}
	
	public function getHost() {
		return $this->host;
	}
	
	public function setHost($host) {
		$this->host = $host;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function setPort($port) {
		$this->prot = $port;
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function setPath($path) {
		$this->path = $path;
	}
	
	public function getQuery() {
		return $this->query;
	}
	
	public function setQuery($query) {
		$this->query = $query;
	}
	
	public function getParams() {
		return $this->params;
	}
	
	public function getParam($key, $cleaned = true) {
		if(isset($this->params[$key]))
			return ($cleaned === true)?strip_tags($this->params[$key]):$this->params[$key];
		
		return null;
	}
	
	public function setParams($params) {
		$this->params = $params;
	}

	
	public function setURI($uri) {
		$this->uri = $uri;
	}
	
	public function getURI() {
		return $this->uri;
	}
	
	public function setUserAgent($userAgent) {
		$this->userAgent = $userAgent;
	}
	
	public function getUserAgent() {
		return $this->userAgent;
	}
	
	public function setLanguages($languages) {
		$this->languages = $languages;
	}
	
	public function getLanguages() {
		return $this->languages;
	}
	
	public function setRequestTime(DateTime $requestTime) {
		$this->requestTime = $requestTime;
	}
	
	public function getRequestTime() {
		return $this->requestTime;
	}
	
	public function setRequestMethod( $requestMethod) {
		$this->requestMethod = $requestMethod;
	}
	
	public function getRequestMethod() {
		return $this->requestMethod;
	}
	
	public function getURL($protocol) {
		return $protocol . '://' . $this->host . $this->uri;
	}

	public function getVar($name, $type = self::VAR_GET) {
		$vars = null;

		if($type === '_GET') {
			$vars = $_GET;
		} elseif($type === '_POST') {
			$vars = $_POST;
		} elseif($type === '_FILES') {
			$vars = $_FILES;
		}

		if(isset($vars[$name]) === false)
			return null;

		return $vars[$name];
	}
}

/* EOF */