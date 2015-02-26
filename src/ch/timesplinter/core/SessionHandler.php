<?php

namespace ch\timesplinter\core;

/**
 * session handler
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
class SessionHandler
{
	protected $core;

	protected $started;
	protected $name;
	protected $ID;

	function __construct(Core $core)
	{
		$this->core = $core;
		
		$this->started = false;
		$this->name = null;
		$this->ID = null;
	}

	public function start()
	{
		if($this->started === true)
			return true;

		if(isset($_COOKIE['PHPSESSID']) === true && $this->checkSessionIdAgainstHashBitsPerChar($_COOKIE['PHPSESSID'], ini_get('session.hash_bits_per_character')) === false) {
			unset($_COOKIE['PHPSESSID']);
		}
		
		if(session_start() === false)
			return false;
		
		$remoteAddr = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:null;
		$userAgent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null;

		if(!isset($_SESSION['TRUSTED_REMOTE_ADDR']) || $_SESSION['TRUSTED_REMOTE_ADDR'] !== $remoteAddr || !isset($_SESSION['PREV_USERAGENT']) || $_SESSION['PREV_USERAGENT'] !== $userAgent)
			$this->regenerateID();

		$this->started = true;
	}

	public function getID()
	{
		if($this->ID === null) {
			$this->ID = session_id();
		}

		return $this->ID;
	}

	public function getName()
	{
		if($this->name === null) {
			$this->name = session_name();
		}

		return $this->name;
	}

	public function regenerateID($deleteOldSession = false)
	{
		if(!isset($_SESSION['TRUSTED_SID']) && session_id() !== '') {
			if(session_destroy() === false || session_start() === false)
				return false;
		}

		session_regenerate_id($deleteOldSession);
		$this->ID = session_id();

		$_SESSION['TRUSTED_SID'] = true;
		$_SESSION['TRUSTED_REMOTE_ADDR'] = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
		$_SESSION['PREV_USERAGENT'] = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
		
		return true;
	}

	public function close()
	{
		if($this->started === true)
			session_write_close();
	}

	/**
	 * Checks session id against valid characters based on the session.hash_bits_per_character ini setting
	 * (http://php.net/manual/en/session.configuration.php#ini.session.hash-bits-per-character)
	 * 
	 * @param string $sessionId The session id to check (for example cookie or get value)
	 * @param int $hashBitsPerChar The session.hash_bits_per_character value (4, 5 or 6)
	 * 
	 * @return bool Returns true if session_id is valid or false if not
	 */
	protected function checkSessionIdAgainstHashBitsPerChar($sessionId, $hashBitsPerChar)
	{
		if($hashBitsPerChar == 4 && preg_match('/^[a-f0-9]+$/', $sessionId) === 0)
			return false;
		elseif($hashBitsPerChar == 5 && preg_match('/^[a-v0-9]+$/', $sessionId) === 0)
			return false;
		elseif($hashBitsPerChar == 6 && preg_match('/^[A-Za-z0-9-,]+$/i', $sessionId) === 0)
			return false;
		
		return true;
	}
}

/* EOF */