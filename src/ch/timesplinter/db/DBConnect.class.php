<?php

namespace ch\timesplinter\db;

/**
 *
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.0.0
 */
class DBConnect {

	private $host;
	private $username;
	private $password;
	private $charset;
	private $database;

	public function __construct($host, $database, $username, $password, $charset = 'UTF8') {
		$this->host = $host;
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;
		$this->charset = $charset;
	}

	public function getHost() {
		return $this->host;
	}

	public function setHost($host) {
		$this->host = $host;
	}

	public function getUsername() {
		return $this->username;
	}

	public function setUsername($username) {
		$this->username = $username;
	}

	public function getPassword() {
		return $this->password;
	}

	public function setPassword($password) {
		$this->password = $password;
	}

	public function getCharset() {
		return $this->charset;
	}

	public function setCharset($charset) {
		$this->charset = $charset;
	}

	public function getDatabase() {
		return $this->database;
	}

	public function setDatabase($database) {
		$this->database = $database;
	}

}

/* EOF */