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
		
	private $requestMethod;
	private $requestTime;
	private $languages;
	private $userAgent;
	private $remoteAddress;

	private $requestVars;
	
	public function __construct() {
		$this->requestVars = array_merge($_GET, $_POST);
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
		$this->port = $port;
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

	public function getRemoteAddress() {
		return $this->remoteAddress;
	}

	public function setRemoteAddress($remoteAddress) {
		$this->remoteAddress = $remoteAddress;
	}
	
	public function getURL($protocol) {
		return $protocol . '://' . $this->host . $this->uri;
	}

	/**
	 * Returns the value of a variable with key $name from either $_GET or $_POST
	 * @param string $name The name of the GET or POST variable
	 * @param string|array $filters Functions to filter the input value
	 * @return mixed|null Returns the value of the variable or null if it does not exist
	 */
	public function getVar($name, $filters = null) {
		if(isset($this->requestVars[$name]) === false)
			return null;

		if($filters === null)
			return $this->requestVars[$name];

		$varValue = $this->requestVars[$name];

		if(is_string($filters) === true)
			return call_user_func($filters, $varValue);

		if(is_array($filters)=== true) {
			foreach($filters as $f)
				$varValue = call_user_func($f, $varValue);

			return $varValue;
		}

		return $varValue;
	}

	public function getFile($name) {
		if(isset($_FILES[$name]) === false)
			return null;

		return $_FILES[$name];
	}
}

/* EOF */