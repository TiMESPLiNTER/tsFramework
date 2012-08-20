<?php

/**
 *
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class DBException extends Exception implements ExceptionHandling {

	private $queryString;
	private $queryParams;

	public function __construct($message, $code, $queryString = '', $queryParams = array()) {
		$this->queryString = $queryString;
		$this->queryParams = $queryParams;

		parent::__construct($message, $code);
	}

	public function getQueryString() {
		return $this->queryString;
	}

	public function getQueryParams() {
		return $this->queryParams;
	}

	public function handleException() {
		header("HTTP/1.1 500 Internal Server Error");
		require_once fwRoot . '/settings/exceptions/DBException.php';
	}

	public function handleExceptionDebug() {
		header("HTTP/1.1 500 Internal Server Error");
		require_once fwRoot . '/settings/exceptions/DBExceptionDebug.php';
	}

}

?>