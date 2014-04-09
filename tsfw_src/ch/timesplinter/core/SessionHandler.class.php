<?php

namespace ch\timesplinter\core;

/**
 * Class SessionHandler
 * @package ch\timesplinter\core
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 */
class SessionHandler {
	private $started;
	private $name;
	private $ID;

	function __construct() {
		$this->started = false;
		$this->name = null;
		$this->ID = null;
	}

	/**
	 * @throws CoreException
	 */
	public function start() {
		if ($this->started === true)
			return;

		if(session_start() === false)
			throw new CoreException('Could not start session');

		$remoteAddress = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null;
		$userAgent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null;

		if (!isset($_SESSION['TRUSTED_REMOTE_ADDR']) || $_SESSION['TRUSTED_REMOTE_ADDR'] !== $remoteAddress || !isset($_SESSION['PREV_USERAGENT']) || $_SESSION['PREV_USERAGENT'] !== $userAgent)
			$this->regenerateID();

		$this->started = true;
	}

	public function getID() {
		if ($this->ID === null) {
			$this->ID = session_id();
		}

		return $this->ID;
	}

	public function getName() {
		if ($this->name === null) {
			$this->name = session_name();
		}

		return $this->name;
	}

	public function regenerateID() {
		if (!isset($_SESSION['TRUSTED_SID']) && session_id() !== '') {
			session_destroy();
			session_start();
		}

		session_regenerate_id();
		$this->ID = session_id();

		$_SESSION['TRUSTED_SID'] = true;
		$_SESSION['TRUSTED_REMOTE_ADDR'] = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
		$_SESSION['PREV_USERAGENT'] = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
	}

	public function close() {
		if ($this->started === true)
			session_write_close();
	}

}

/* EOF */