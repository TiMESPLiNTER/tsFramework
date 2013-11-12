<?php
namespace ch\timesplinter\core;

/**
 * Description of HttpResponse
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright (c) 2012, Pascal Münst
 * @version 1.0
 */
class HttpResponse {
	private static $STATUS_CODES = array(
		// 1xx - Informations
		 100 => 'Continue'
		,101 => 'Switching Protocols'
		,102 => 'Processing'
		,118 => 'Connection timed out'

		// 2xx - Successful operation
		,200 => 'OK'
		,201 => 'Created'
		,202 => 'Accepted'
		,203 => 'Non-Authorative Information'
		,204 => 'No Content'
		,205 => 'Reset Content'
		,206 => 'Partial Content'
		,207 => 'Multi-Status'

		// 3xx - Redirection
		,300 => 'Multiple Choices'
		,301 => 'Moved Permanently'
		,302 => 'Found'
		,303 => 'See Other'
		,304 => 'Not Modified'
		,305 => 'Use Proxy'
		,306 => 'Switch Proxy'
		,307 => 'Temporary Redirect'

		// 4xx - Client errors
		,400 => 'Bad Request'
		,401 => 'Unauthorized'
		,402 => 'Payment Required'
		,403 => 'Forbidden'
		,404 => 'Not Found'
		,405 => 'Method Not Allowed'
		,406 => 'Not Acceptable'
		,407 => 'Proxy Authentication Required'
		,408 => 'Request Time-out'
		,409 => 'Conflict'
		,410 => 'Gone'
		,411 => 'Length Required'
		,412 => 'Precondition Failed'
		,413 => 'Request Entity Too Large'
		,414 => 'Reuqest-URL Too Long'
		,415 => 'Unsupported Media Type'
		,416 => 'Requested range not satisfiable'
		,417 => 'Expectation Failed'
		,421 => 'There are too many connections from your internet address'
		,422 => 'Unprocessable Entity'
		,423 => 'Locked'
		,424 => 'Failed Dependency'
		,425 => 'Unordered Collection'
		,426 => 'Upgrade Required'
		,451 => 'Unavailable For Legal Reason'

		// 5xx - server errors
		,500 => 'Internal Server Error'
		,501 => 'Not Implemented'
		,502 => 'Bad Gateway'
		,503 => 'Service Unavailable'
		,504 => 'Gateway Time-out'
		,505 => 'HTTP Version not supported'
		,506 => 'Variant Also Negotiates'
		,507 => 'Insufficient Storage'
		,509 => 'Bandwidth Limit Exceeded'
		,510 => 'Not Extended'
	);

	private $httpStatusCode;
	private $headers;
	private $cookies;
	private $content;
	private $stream;
	private $streamContext;

	/**
	 *
	 * @param int $httpStatusCode
	 * @param string $content
	 * @param array $headers
	 * @param boolean $stream
	 * @param mixed $streamContext
	 * @internal param int $httpResponseCode
	 * @return HttpResponse
	 */
	public function __construct($httpStatusCode = 200, $content = null, $headers = array('Content-Type' => 'text/html; charset=UTF-8'), $stream = false, $streamContext = null) {
		$this->httpStatusCode = $httpStatusCode;
		$this->headers = $headers;
		$this->cookies = array();
		$this->content = $content;
		$this->stream = $stream;
		$this->streamContext = $streamContext;
	}
	
	public function getHeaders() {
		return $this->headers;
	}
	
	public function addHeader($key, $value) {
		$this->headers[$key] = $value;
	}
	
	public function setHeaders($headers) {
		$this->headers = $headers;
	}

	public function removeHeader($key) {
		header_remove($key);
	}

	public function removeAllHeaders() {
		header_remove();
	}

	public function addCookie(Cookie $cookie) {
		$this->cookies[$cookie->getName()] = $cookie;
	}

	public function setCookies(array $cookies) {
		$this->cookies = array();

		foreach($cookies as $cookie)
			$this->cookies[$cookie->getName()] = $cookie;
	}

	public function removeCookie($name) {
		if(array_key_exists($name, $_COOKIE) === true)
			unset($_COOKIE[$name]);

		$this->addCookie(new Cookie($name, null, time()-3600));
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function setContent($content) {
		$this->content = $content;
	}
	
	public function getHttpStatusCode() {
		return $this->httpStatusCode;
	}
	
	public function setHttpResponseCode($httpStatusCode) {
		$this->httpStatusCode = $httpStatusCode;
	}
	
	public function send() {
		header($this->getHttpStatusHeader($this->httpStatusCode));

		foreach($this->headers as $key => $value) {
			if($value === null)
				header_remove($key);
			else
				header($key . ': ' . $value);
		}

		foreach($this->cookies as $cookie) {
			/** @var Cookie $cookie */
			setcookie(
				$cookie->getName(),
				$cookie->getValue(),
				$cookie->getExpire(),
				$cookie->getPath(),
				$cookie->getDomain(),
				$cookie->getSecure(),
				$cookie->getHttpOnly()
			);
		}
		
		if($this->content === null)
			return;

		if($this->stream === false) {
			echo $this->content;
		} else {
			readfile($this->content, false, $this->streamContext);
		}
	}

	public static function getHttpStatusString($statusCode) {
		return (isset(self::$STATUS_CODES[$statusCode])?' ' . self::$STATUS_CODES[$statusCode]:null);
	}

	private function getHttpStatusHeader($statusCode) {
		return 'HTTP/1.1 ' . $statusCode . $this::getHttpStatusString($statusCode);
	}
}

/* EOF */