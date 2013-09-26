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
	private $remoteAddress;

	private $requestVars;
	private $cookies;
	
	public function __construct() {
		$this->params = array();
		$this->requestVars = array_merge($_GET, $_POST);
		$this->cookies = $_COOKIE;
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
	 * @param string|array|null $filters Function names or valid callbacks to filter the input value
	 * @return mixed|null Returns the value of the variable or null if it does not exist
	 */
	public function getVar($name, $filters = null) {
		if(isset($this->requestVars[$name]) === false)
			return null;

		return ($filters === null)?$this->requestVars[$name]:$this->sanitize($this->requestVars[$name]);
	}

	/**
	 * Returns the value of a cookie with key $name
	 * @param $name The name of the cookie
	 * @param string|array|null $filters Function names or valid callbacks to filter the input value
	 * @return mixed|null Returns the value of the cookie or null if it does not exist
	 */
	public function getCookieValue($name, $filters = null) {
		if(isset($this->cookies[$name]) === false)
			return null;

		return ($filters === null)?$this->cookies[$name]:$this->sanitize($this->cookies[$name]);
	}

	/**
	 * Returns the informations about a file
	 * @param $name The name of the file field
	 * @return array|null Returns the informations about the file or null if it does not exist
	 */
	public function getFile($name) {
		if(isset($_FILES[$name]) === false)
			return null;

		return $_FILES[$name];
	}

	private function sanitize($value, $filters) {
		if(is_string($filters) === true)
			return call_user_func($filters, $value);

		if(is_array($filters) === true) {
			$valueFiltered = $value;

			foreach($filters as $f)
				$valueFiltered = call_user_func($f, $valueFiltered);

			return $valueFiltered;
		}

		return $value;
	}
}

/* EOF */