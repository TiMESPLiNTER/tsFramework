<?php

namespace ch\timesplinter\auth;

use ch\timesplinter\core\SessionHandler;
use ch\timesplinter\logger\Logger;

/**
 * @author Pascal Münst <info@actra.ch>
 * @copyright Copyright (c) 2012 Actra AG
 */
abstract class AuthHandler
{
	/** @var boolean */
	protected $loggedIn;
	protected $lastAction;
	protected $loginPopo;
	/** @var array */
	protected $settings;

	/** @var SessionHandler $sessionHandler */
	protected $sessionHandler;

	public function __construct(SessionHandler $sessionHandler, array $settings = array())
	{
		$this->sessionHandler = $sessionHandler;

		$this->settings = $settings + array(
			'session_max_idle_time' => 180,
			'login_site' => '/login.html'
		);

		$this->loggedIn = isset($_SESSION['auth']['logged_in']) ? $_SESSION['auth']['logged_in'] : false;
		$this->lastAction = isset($_SESSION['auth']['last_action']) ? $_SESSION['auth']['last_action'] : time();

		if($this->loggedIn === true && $this->settings['session_max_idle_time'] > 0 && $this->lastAction + $this->settings['session_max_idle_time'] <= time()) {
			$this->logout();

			header('HTTP/1.1 401 Unauthorized');
			header('Location: ' . $this->settings['login_site'] . '?session_expired');
			exit;
		}

		$this->lastAction = time();
	}

	/**
	 * Checks the given login parameters against the login information stored in DB or txt
	 *
	 * @param string $username
	 * @param string $password
	 * @param callable|\Closure|null $callbackOnSuccess
	 *
	 * @return bool
	 */
	public abstract function checkLogin($username, $password, $callbackOnSuccess = null);

	/**
	 * Returns the login state of the current user
	 *
	 * @return boolean
	 */
	public function isLoggedIn()
	{
		return $this->loggedIn;
	}

	public function logout()
	{
		$this->sessionHandler->regenerateID(true);

		$this->loggedIn = false;
		$this->loginPopo = null;
		$this->lastAction = null;
	}

	/**
	 *
	 * @return \stdClass
	 */
	public function getUserData()
	{
		return $this->loginPopo;
	}

	public function __destruct()
	{
		$_SESSION['auth']['logged_in'] = $this->loggedIn;
		$_SESSION['auth']['last_action'] = $this->lastAction;
	}
}

/* EOF */