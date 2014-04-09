<?php

namespace ch\timesplinter\auth;

use ch\timesplinter\auth\AuthHandler;
use ch\timesplinter\core\SessionHandler;
use ch\timesplinter\db\DB;
use ch\timesplinter\db\DBException;

/**
 * Class AuthHandlerDB
 * @package ch\timesplinter\auth
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
class AuthHandlerDB extends AuthHandler {
	const HASH_TYPE = 'sha256';
	const LOGIN_SITE = '/login.html';
	const DEFAULT_SITE_AFTER_LOGIN = '/';

	protected $userId;
	/* @var $db DB */
	protected $db;
	/** @var SessionHandler $sessionHandler */
	protected $sessionHandler;

	protected $onLoginSuccessCallback;

	public function __construct(DB $db, SessionHandler $sessionHandler) {
		parent::__construct();

		$this->db = $db;
		$this->sessionHandler = $sessionHandler;
		$this->userId = (isset($_SESSION['userid']))?$_SESSION['userid']:0;
	}
	
	public static function encryptPassword($password, $salt) {	
		return hash(self::HASH_TYPE, $salt . $password);
	}
	
	public function checkLogin($email, $password) {
		$stmntLogin = $this->db->prepare("
			SELECT ID
				,email
				,password
				,active
				,salt
				,registered
				,confirmed
				,wronglogins
				,lastlogin
				,token
			FROM login
			WHERE email = ?
			AND confirmed IS NOT NULL
		");
		$loginRes = $this->db->select($stmntLogin, array($email));

		if(count($loginRes) <= 0)
			return false;

		$this->loginPopo = $loginRes[0];

		if($this->loginPopo->wronglogins >= 5)
			return false;

		$inputPwHash = $this->encryptPassword($password, $this->loginPopo->salt);

		if($inputPwHash !== $this->loginPopo->password) {
			// Wrong login update
			++$this->loginPopo->wronglogins;

			$stmntUpdateWrongLogins = $this->db->prepare("UPDATE login SET wronglogins = ? WHERE ID = ?");
			$this->db->update($stmntUpdateWrongLogins, array(
				 $this->loginPopo->wronglogins
				,$this->loginPopo->ID
			));

			return false;
		}

		if($this->loginPopo->active != 1)
			return false;

		// Security!
		$this->sessionHandler->regenerateID();

		// Alles i.O.
		$this->loggedIn = $_SESSION['loggedin'] = true;
		$this->userId = $_SESSION['userid'] = $this->loginPopo->ID;

		// Save old last login date
		$_SESSION['lastlogin'] = $this->loginPopo->lastlogin;

		// Update new lastlogin
		$lastLoginUpdateStmnt = $this->db->prepare("UPDATE login SET lastlogin = NOW() WHERE ID = ?");
		$this->db->update($lastLoginUpdateStmnt, array($this->userId));

		// Reset wrong login counter
		//$this->loginPopo->wronglogins = 0;
		//$this->loginPopo->lastlogin = date('Y-m-d H:i:s');
		$stmntUpdateLogin = $this->db->prepare("UPDATE login SET wronglogins = 0 AND lastlogin = NOW() WHERE ID = ?");
		$this->db->update($stmntUpdateLogin, array($this->loginPopo->ID));

		if($this->onLoginSuccessCallback !== null)
			call_user_func_array($this->onLoginSuccessCallback, array($this));

		// Go to the page the user came from
		return true;
	}

	protected function loadUserPopo() {
		$stmntLogin = $this->db->prepare(
				"SELECT ID
					   ,email
					   ,password
					   ,active
					   ,salt
					   ,registered
					   ,confirmed
					   ,wronglogins
					   ,lastlogin
					   ,token
				FROM login 
				WHERE ID = ? 
				  AND confirmed IS NOT NULL");
		$loginRes = $this->db->select($stmntLogin, array($this->userId));
		
		if(count($loginRes) <= 0) {
			$this->logout();
			
			if(strpos(basename($_SERVER['REQUEST_URI']), self::LOGIN_SITE) !== 0) {
				$_SESSION['pageAfterLogin'] = $_SERVER['REQUEST_URI'];
				
				header('Location: ' . self::LOGIN_SITE);
				exit;
			}
			
			$this->loginPopo = null;
			return;
		}
				
		$this->loginPopo = $loginRes[0];

		// Restore old last login date from current session
		$this->loginPopo->lastlogin = array_key_exists('lastlogin', $_SESSION)?$_SESSION['lastlogin']:null;

		if($this->onUserDataLoadCallback !== null)
			call_user_func_array($this->onUserDataLoadCallback, array($this->loginPopo));
	}
	
	public function signUp($login) {
		$stmntSingup = $this->db->prepare("
			INSERT INTO login SET
				email = ?,
				password = ?,
				registeredby = ?,
				salt = ?,
				confirmed = NULL,
				active = ?,
				lastlogin = NULL,
				wronglogins = 0,
				token = ?,
				tokentime = ?
		");
		
		$salt = $this->generateSalt();
		
		try {
			$this->db->beginTransaction();
			
			$userID = $this->db->insert($stmntSingup, array(
				$login->email,
				isset($login->password)?$this->encryptPassword($login->password, $salt):null,
				isset($login->registeredBy)?$login->registeredBy:null,
				$salt,
				isset($login->active)?$login->active:0,
				isset($login->token)?$login->token:null,
				isset($login->token)?date('Y-m-d H:i:s'):null
			));
			
			if(!isset($login->rightGroups) || !is_array($login->rightGroups)) {
				$this->db->commit();
				return $userID;
			}
			
			$stmntRghtGrps = $this->db->prepare("
				INSERT INTO login_has_rightgroup SET
					 loginIDFK = ?
					,rightgroupIDFK = ?
					,datefrom = NOW() 
			");
			
			foreach($login->rightGroups as $rg) {
				$this->db->insert($stmntRghtGrps, array($userID, $rg));
			}
			
			$this->db->commit();
			
			return $userID;
		} catch(\Exception $e) {
			$this->db->rollBack();
			
			throw $e;
		}
		
		return false;
	}
	
	public static function generateSalt() {
		$salt = '';
		
		for($i = 0; $i < 32; ++$i)
			$salt .= chr(rand(0,127));
		
		return $salt;
	}
	
	public function getMessage() {
		return $this->message;
	}

	public function logout() {
		session_destroy();
		
		$this->sessionHandler->regenerateID();

		$this->loggedIn = false;
		$this->loginPopo = null;

		return true;
	}

	/**
	 * @param $email The email address of the account
	 * @return bool Does the account exists or not
	 */
	public function accountExists($email) {
		// E-Mail check
		$stmntEmailCheck = $this->db->prepare("SELECT ID FROM login WHERE email = ?");
		$resEmailCheck = $this->db->select($stmntEmailCheck, array($email));

		return (count($resEmailCheck) > 0)?$resEmailCheck[0]->ID:false;
	}

	/**
	 * @param $userID
	 * @internal param \ch\timesplinter\auth\The $email email address of the account the token should be generated
	 * @return string The token
	 */
	public function generateToken($userID) {
		$token = uniqid();

		$stmntToken = $this->db->prepare("UPDATE login SET token = ?, tokentime = NOW() WHERE ID = ?");
		$this->db->update($stmntToken, array($token, $userID));

		return $token;
	}

	public function checkToken($token, $userID) {
		$stmntTokenCheck = $this->db->prepare("
			SELECT ID
			FROM login
			WHERE ID = ?
			AND token = ?
			AND DATE_ADD(tokentime, INTERVAL 1 DAY) >= NOW()
		");

		$resTokenCheck = $this->db->select($stmntTokenCheck, array($userID, $token));

		return (count($resTokenCheck) > 0);
	}

	public function resetPassword($userId, $password) {
		$stmntResetPassword = $this->db->prepare("
			UPDATE login SET password = ?, salt = ?, token = NULL, wronglogins = 0 WHERE ID = ?
		");

		$salt = $this->generateSalt();

		$resResetPassword = $this->db->update($stmntResetPassword, array(
			$this->encryptPassword($password, $salt), $salt, $userId
		));

		return ($resResetPassword > 0);
	}

	public function getUserID() {
		return $this->userId;
	}

	public function addOnLoginSuccessCallback(array $onLoginSuccessCallback) {
		$this->onLoginSuccessCallback = $onLoginSuccessCallback;
	}
}

/* EOF */
