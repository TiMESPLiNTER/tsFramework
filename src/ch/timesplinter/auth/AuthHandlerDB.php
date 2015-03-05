<?php

namespace ch\timesplinter\auth;

use timesplinter\tsfw\db\DBException;
use ch\timesplinter\core\SessionHandler;
use timesplinter\tsfw\db\DB;

/**
 * @author Pascal Münst <info@actra.ch>
 * @copyright 2011 Actra AG
 */
class AuthHandlerDB extends AuthHandler
{
	protected $userId;
	protected $lastLogin;
	/* @var $db DB */
	protected $db;

	public function __construct(DB $db, SessionHandler $sessionHandler, array $settings = array())
	{
		parent::__construct($sessionHandler, $settings + array(
				'hash_type' => 'sha256',
				'db_login_table' => 'login'
			));

		$this->db = $db;
		$this->sessionHandler = $sessionHandler;
		$this->userId = isset($_SESSION['auth']['user_id']) ? $_SESSION['auth']['user_id'] : 0;
		$this->lastLogin = isset($_SESSION['auth']['last_login']) ? $_SESSION['auth']['last_login'] : null;

		if($this->loggedIn === true)
			$this->loadUserPopo();
	}

	public function encryptPassword($password, $salt)
	{
		return hash($this->settings['hash_type'], $salt . $password);
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkLogin($username, $password, $callbackOnSuccess = null)
	{
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
				FROM " . $this->settings['db_login_table'] . " 
				WHERE email = ? 
				AND confirmed IS NOT NULL
			");
			$loginRes = $this->db->select($stmntLogin, array($username));

			if(count($loginRes) <= 0)
				return false;

			$loginData = $loginRes[0];

			if($loginData->wronglogins >= 5)
				return false;

			$inputPwHash = $this->encryptPassword($password, $loginData->salt);

			if($inputPwHash !== $loginData->password) {
				// Wrong login update
				++$loginData->wronglogins;

				$stmntUpdateWrongLogins = $this->db->prepare("
					UPDATE " . $this->settings['db_login_table'] . " SET wronglogins = ? WHERE ID = ?
				");

				$this->db->update($stmntUpdateWrongLogins, array(
					$loginData->wronglogins,
					$loginData->ID
				));

				return false;
			}

			if($loginData->active != 1)
				return false;

			// Security!
			$this->sessionHandler->regenerateID();

			// Alles i.O.
			$this->loggedIn = true;
			$this->userId = $loginData->ID;

			// Save old last login date
			$this->lastLogin = $loginData->lastlogin;

			// Reset wrong login counter and update lastlogin
			$stmntUpdateLogin = $this->db->prepare("
				UPDATE " . $this->settings['db_login_table'] . " SET wronglogins = 0 AND lastlogin = NOW() WHERE ID = ?
			");
			$this->db->update($stmntUpdateLogin, array($loginData->ID));

			$this->loadUserPopo();

			return true;
		} catch(DBException $e) {

		}

		return false;
	}

	protected function loadUserPopo()
	{
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
				FROM " . $this->settings['db_login_table'] . " 
				WHERE ID = ? 
				  AND confirmed IS NOT NULL");
		$loginRes = $this->db->select($stmntLogin, array($this->userId));

		if(count($loginRes) <= 0) {
			$this->logout();

			if(strpos(basename($_SERVER['REQUEST_URI']), $this->settings['login_site']) !== 0) {
				$_SESSION['pageAfterLogin'] = $_SERVER['REQUEST_URI'];

				header('HTTP/1.1 401 Unauthorized');
				header('Location: ' . $this->settings['login_site'] . '?no_user');
				exit;
			}

			$this->loginPopo = null;
			return;
		}

		$this->loginPopo = $loginRes[0];

		// Restore old last login date from current session
		$this->loginPopo->lastlogin = $this->lastLogin;
	}

	/**
	 * @param \stdClass $login
	 *
	 * @return int
	 *
	 * @throws \Exception
	 */
	public function signUp($login)
	{
		$stmntSingup = $this->db->prepare("
			INSERT INTO login SET
				username = ?,
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
				isset($login->username)?$login->username:null,
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
	}

	/**
	 * Generates a random string which can be used to salt the password
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	public static function generateSalt($length = 32)
	{
		$salt = '';

		for($i = 0; $i < $length; ++$i)
			$salt .= chr(rand(0,127));

		return $salt;
	}

	/**
	 * @param string $email The email address of the account
	 *
	 * @return bool Does the account exists or not
	 */
	public function accountExists($email)
	{
		// E-Mail check
		$stmntEmailCheck = $this->db->prepare("SELECT ID FROM " . $this->settings['db_login_table'] . " WHERE email = ?");
		$resEmailCheck = $this->db->select($stmntEmailCheck, array($email));

		return (count($resEmailCheck) > 0)?$resEmailCheck[0]->ID:false;
	}

	/**
	 * @param string $userID The email address of the account the token should be generated
	 *
	 * @return string The token
	 */
	public function generateToken($userID) {
		$token = uniqid();

		$stmntToken = $this->db->prepare("UPDATE " . $this->settings['db_login_table'] . " SET token = ?, tokentime = NOW() WHERE ID = ?");
		$this->db->update($stmntToken, array($token, $userID));

		return $token;
	}

	public function checkToken($token, $userID)
	{
		$stmntTokenCheck = $this->db->prepare("
			SELECT ID
			FROM " . $this->settings['db_login_table'] . "
			WHERE ID = ?
			AND token = ?
			AND DATE_ADD(tokentime, INTERVAL 1 DAY) >= NOW()
		");

		$resTokenCheck = $this->db->select($stmntTokenCheck, array($userID, $token));

		return (count($resTokenCheck) > 0);
	}

	public function getUserID()
	{
		return $this->userId;
	}

	public function __destruct()
	{
		parent::__destruct();

		$_SESSION['auth']['last_login'] = $this->lastLogin;
		$_SESSION['auth']['user_id'] = $this->userId;
	}
}

/* EOF */