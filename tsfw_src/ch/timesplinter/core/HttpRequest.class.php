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
	
	public function getParam($key) {
		if(isset($this->params[$key]))
			return $this->params[$key];
		
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
}

/* EOF */