<?php
namespace ch\timesplinter\auth;

use ch\timesplinter\auth\AuthHandler;

/**
 * This class implements a AuthHandler based on the HTTP_AUTH_DIGEST method
 * username and passwords are stored in a text file. 
 * 
 * IMPORTANT: Please place the username/password-file outside of the www-root 
 * (for your own safety!). If this is not possible please make sure the file is
 * at least not accessable over external requests to your server!
 *
 * @author Pascal Münst (Actra AG)
 * @copyright Copyright (c) 2011, Actra AG
 * @version
 */
class AuthHandlerHttp extends AuthHandler {
	/** @var ErrorHandler */
	private $errorHandler;
	
	private $benutzer;
	private $realm;
	
	public function __construct() {
		parent::__construct();
		
		$this->errorHandler = ErrorHandler::getInstance();
	
		$this->realm = 'My Realm';
		
		// In File auslagern
		$this->benutzer = self::loadFromFile(); //array('admin' => 'mypass', 'gast' => 'gast');
	}
	
	private function loadFromFile() {
		$accFile = dirname(__FILE__) . '/accounts.txt';
		
		$fileContent = file($accFile,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		
		$users = array();
		
		foreach($fileContent as $l) {
			$lParts = explode(':',$l);
			
			$users[trim($lParts[0])] = trim($lParts[1]);
		}
		
		return $users;
	}
	
	public function isLoggedIn() {
		if($this->loggedIn === false) {
			if(!isset($_SERVER['PHP_AUTH_DIGEST']) || empty($_SERVER['PHP_AUTH_DIGEST']) || !self::checkLogin(null,null)) {
				header('WWW-Authenticate: Digest realm="' . $this->realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($this->realm) . '"');
				$this->errorHandler->display_error(401);
				exit;
			}
		}
		
		return $this->loggedIn;
	}

	public function checkLogin($username, $password) {
		// Analysieren der Variable PHP_AUTH_DIGEST
		$daten = self::http_digest_parse($_SERVER['PHP_AUTH_DIGEST']);
				
		if ($daten === false || !isset($this->benutzer[$daten['username']]))
			return false;

		// Erzeugen einer gültigen Antwort
		$A1 = md5($daten['username'] . ':' . $this->realm . ':' . $this->benutzer[$daten['username']]);
		$A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $daten['uri']);
		
		$gueltige_antwort = md5($A1 . ':' . $daten['nonce'] . ':' . $daten['nc'] .
								':' . $daten['cnonce'] . ':' . $daten['qop'] . ':' .
								$A2);
		
		if ($daten['response'] != $gueltige_antwort)
			return false;
		
		// Alles i.O.
		$this->loggedIn = $_SESSION['loggedin'] = true;
		
		return true;
	}

	public function checkUG($userGroup) {
		
	}
	
	// Funktion zum analysieren der HTTP-Auth-Header
	private function http_digest_parse($txt) {
		// gegen fehlende Daten schützen
		$noetige_teile = array('nonce' => 1
							  ,'nc' => 1
							  ,'cnonce' => 1
							  ,'qop' => 1
							  ,'username' => 1
							  ,'uri' => 1
							  ,'response' => 1
		);
		
		$daten = array();
		$schluessel = implode('|', array_keys($noetige_teile));

		preg_match_all('@(' . $schluessel . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $treffer, PREG_SET_ORDER);

		foreach ($treffer as $t) {
			$daten[$t[1]] = $t[3] ? $t[3] : $t[4];
			unset($noetige_teile[$t[1]]);
		}
		
		return $noetige_teile ? false : $daten;
	}

	public function logout() {
		echo 'not implemented';
		exit;
	}
}

?>
