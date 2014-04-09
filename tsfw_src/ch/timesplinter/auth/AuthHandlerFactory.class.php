<?php

namespace ch\timesplinter\auth;

use ch\timesplinter\core\SessionHandler;
use ch\timesplinter\db\DB;

/**
 * Class AuthHandlerFactory
 * @package ch\timesplinter\auth
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
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

/* EOF */