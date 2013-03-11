<?php
namespace ch\timesplinter\auth;

use ch\timesplinter\auth\AuthHandler;
use ch\timesplinter\db\DBException;
use ch\timesplinter\core\SessionHandler;
use ch\timesplinter\db\DB;
/**
 * Required packages: backend
 *
 * @author Pascal Münst
 * @copyright 2011 Actra AG
 * @version 1.0
 */
class AuthHandlerDB extends AuthHandler {
	const HASH_TYPE = 'sha256';
	const LOGIN_SITE = '/login.html';
	const DEFAULT_SITE_AFTER_LOGIN = '/home.html?n=uebersicht';
	
	private $userId;
	/* @var $db DB */
	private $db;
	/** @var SessionHandler $sessionHandler */
	private $sessionHandler;
	
	public function __construct(DB $db, SessionHandler $sessionHandler) {
		parent::__construct();
		
		$this->db = $db;
		$this->sessionHandler = $sessionHandler;
		$this->userId = (isset($_SESSION['userid']))?$_SESSION['userid']:-1;
		
		if($this->loggedIn === true)
			self::loadUserPopo();
	}
	
	public static function encryptPassword($password, $salt) {	
		return hash(self::HASH_TYPE, $salt . $password);
	}
	
	public function checkLogin($username, $password, $callbackOnSuccess = null) {
		try {
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
			$loginRes = $this->db->select($stmntLogin, array($username));
			
			if(count($loginRes) <= 0)
				return false;
			
			$this->loginPopo = $loginRes[0];
			
			if($this->loginPopo->wronglogins >= 5)
				return false;
			
			$inputPwHash = self::encryptPassword($password, $this->loginPopo->salt);
			
			if($inputPwHash !== $this->loginPopo->password) {
				// Woring login update
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
			
			$this->loginPopo->rightgroups = self::loadRightGroups($this->loginPopo->ID);
			
			// Security!
			$this->sessionHandler->regenerateID();
		
			// Alles i.O.
			$this->loggedIn = $_SESSION['loggedin'] = true;
			$this->userId = $_SESSION['userid'] = $this->loginPopo->ID;

			// Save old last login date
			$_SESSION['lastlogin'] = $this->loginPopo->lastlogin;
			
			// Reset wrong login counter
			//$this->loginPopo->wronglogins = 0;
			//$this->loginPopo->lastlogin = date('Y-m-d H:i:s');
			$stmntUpdateLogin = $this->db->prepare("UPDATE login SET wronglogins = 0 AND lastlogin = NOW() WHERE ID = ?");
			$this->db->update($stmntUpdateLogin, array($this->loginPopo->ID));
			
			if($callbackOnSuccess !== null && is_array($callbackOnSuccess) === true) {
				call_user_func_array($callbackOnSuccess, array($this));
			}
			
			// Go to the page the user came from
			return true;
		} catch(DBException $e) {
			$this->logger->error('Bad db query while login', $e);
			return false;
		}
		
		return false;
	}
	
	private function loadRightGroups($userID) {
		// Load grps
		$stmntLoginGroups = $this->db->prepare(
			"SELECT loginIDFK
				   ,rightgroupIDFK
				   ,datefrom
				   ,dateto
				   ,groupkey
				   ,groupname
			FROM login_has_rightgroup lr
			LEFT JOIN rightgroup r ON r.ID = lr.rightgroupIDFK
			WHERE loginIDFK = ? 
			  AND datefrom <= NOW() 
			  AND (dateto >= NOW() OR dateto IS NULL)");
		
		return $this->db->select($stmntLoginGroups, array($userID));
	}
	
	public function checkUG($userGroup) {
		if($this->loginPopo === null)
			return ($userGroup == 'visitor');
		
		if($userGroup == 'user' && $this->loggedIn === true)
			return true;
		
		$groupEntries = $this->loginPopo->rightgroups;
		
		if($userGroup === 'visitor' && count($groupEntries) === 0)
			return true;
		
		foreach($groupEntries as $rg) {
			if($userGroup === $rg->groupkey)
				return true;
		}
		
		return false;
	}
		
	private function loadUserPopo() {
		$stmntLogin = $this->db->prepare(
				"SELECT ID
					   ,username
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
			self::logout();
			
			if(strpos(basename($_SERVER['REQUEST_URI']), self::LOGIN_SITE) !== 0) {
				$_SESSION['pageAfterLogin'] = $_SERVER['REQUEST_URI'];
				
				header('Location: ' . self::LOGIN_SITE);
				exit;
			}
			
			$this->loginPopo = null;
			return;
		}
				
		$this->loginPopo = $loginRes[0];
		$this->loginPopo->rightgroups = self::loadRightGroups($this->loginPopo->ID);
		// Restore old last login date from current session
		$this->loginPopo->lastlogin = array_key_exists('lastlogin', $_SESSION)?$_SESSION['lastlogin']:null;
	}
	
	public function signUp($login) {
		$stmntSingup = $this->db->prepare("
			INSERT INTO login SET
				username = ?,
				email = ?,
				password = ?,
				registeredby = ?,
				salt = ?,
				confirmed = NULL,
				active = 0,
				lastlogin = NULL,
				wronglogins = 0,
				token = NULL
		");
		
		$salt = self::generateSalt();
		
		try {
			$this->db->beginTransaction();
			
			$userID = $this->db->insert($stmntSingup, array(
				isset($login->username)?$login->username:null,
				$login->email,
				self::encryptPassword($login->password, $salt),
				isset($login->registeredBy)?$login->registeredBy:null,
				$salt
			));
			
			if(!isset($login->rightGroups) || !is_array($login->rightGroups))
				return $userID;
			
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
		} catch(DBException $e) {
			$this->db->rollBack();
			
			return false;
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
	}
}

?>