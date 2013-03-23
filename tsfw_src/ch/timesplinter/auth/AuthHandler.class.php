<?php
namespace ch\timesplinter\auth;

use ch\timesplinter\logger\LoggerFactory;

/**
 * Required packages: core, logger
 *
 * @author Pascal Münst (Actra AG)
 * @copyright
 * @version
 */
abstract class AuthHandler {
	const TYPE_HTTP = 'http';
	const TYPE_DB = 'db';
	
	/** @var boolean */
	protected $loggedIn;
	protected $loginPopo;
	/** @var Logger */
	protected $logger;
	
	public function __construct() {
		$this->logger = LoggerFactory::getLoggerByName('dev', $this);
		$this->loggedIn = (isset($_SESSION['loggedin']))?$_SESSION['loggedin']:false;
	}
	
	/**
	 * Checks the given login paramters against the login information stored
	 * in DB or txt
	 * @param string $username
	 * @param string $password
	 */
	abstract function checkLogin($username,$password);
	
	abstract function logout();
	
	/**
	 * Returns the loggin state of the current user
	 * @return boolean 
	 */
	public function isLoggedIn() {
		return $this->loggedIn;
	}
	
	/**
	 * Checks if the current user is in the group $userGroup
	 * @param string userGroup
	 * @return boolean 
	 */
	abstract function hasRightgroup($userGroup);
	
	/**
	 *
	 * @return UserPopo
	 */
	public function getUserData() {
		return $this->loginPopo;
	}
}

/* EOF */