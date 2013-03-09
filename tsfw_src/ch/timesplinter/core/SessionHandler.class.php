<?php

namespace ch\timesplinter\core;

/**
 * session handler
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
class SessionHandler {
	private $core; 
	
	private $started;
	private $name;
	private $ID;

	function __construct(Core $core) {
		$this->core = $core;
		
		$this->started = false;
		$this->name = null;
		$this->ID = null;
	}

	public function start() {
		if ($this->started === true)
			return;

		session_start();

		$remoteAddr = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null;
		$userAgent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null;

		if (!isset($_SESSION['TRUSTED_REMOTE_ADDR']) || $_SESSION['TRUSTED_REMOTE_ADDR'] !== $remoteAddr || !isset($_SESSION['PREV_USERAGENT']) || $_SESSION['PREV_USERAGENT'] !== $userAgent) {
			$this->regenerateID();
		}

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