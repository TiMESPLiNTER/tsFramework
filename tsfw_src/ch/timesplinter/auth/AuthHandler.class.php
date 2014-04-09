<?php

namespace ch\timesplinter\auth;

use ch\timesplinter\core\FrameworkLoggerFactory;
use ch\timesplinter\logger\LoggerFactory;

/**
 * Class AuthHandler
 * @package ch\timesplinter\auth
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
abstract class AuthHandler {
	const TYPE_HTTP = 'http';
	const TYPE_DB = 'db';
	
	/** @var boolean */
	protected $loggedIn;
	protected $loginPopo;
	/** @var Logger */
	protected $logger;

	protected $onUserDataLoadCallback;
	
	public function __construct() {
		$this->logger = FrameworkLoggerFactory::getLogger($this);
		$this->loggedIn = (isset($_SESSION['loggedin']))?$_SESSION['loggedin']:false;
	}
	
	/**
	 * Checks the given login paramters against the login information stored
	 * in DB or txt
	 * @param string $email
	 * @param string $password
	 */
	abstract function checkLogin($email,$password);
	
	abstract function logout();
	
	/**
	 * Returns the loggin state of the current user
	 * @return boolean 
	 */
	public function isLoggedIn() {
		return $this->loggedIn;
	}
	
	/**
	 *
	 * @return UserPopo
	 */
	public function getUserData() {
		if($this->loggedIn === true)
			$this->loadUserPopo();

		return $this->loginPopo;
	}

	public function addOnUserDataLoadCallback(array $onUserDataLoadCallback) {
		$this->onUserDataLoadCallback = $onUserDataLoadCallback;
	}
}

/* EOF */