<?php
namespace ch\timesplinter\auth;

use ch\timesplinter\core\SessionHandler;
use ch\timesplinter\db\DB;

/**
 * Description of AuthHandlerFactory
 *
 * @author Pascal Münst (Actra AG)
 * @copyright
 * @version 1.0.0
 */
class AuthHandlerFactory {
	private static $instance = null;
	
	public static function getInstance($type, SessionHandler $sessionHandler, DB $connection = null) {
		if(self::$instance === null) {
			switch($type) {
				case 'db':
					self::$instance = new AuthHandlerDB($connection,$sessionHandler);
					break;
				case 'http':
					self::$instance = new AuthHandlerHttp;
					break;
				default:
					break;
			}
		}
		
		return self::$instance;
	}
}

?>
