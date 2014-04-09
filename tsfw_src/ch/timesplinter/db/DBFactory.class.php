<?php

namespace ch\timesplinter\db;

use ch\timesplinter\db\DBMySQL;
use ch\timesplinter\db\DBConnect;
use ch\timesplinter\db\DBException;

use \Swift_Mime_SimpleMessage;
use \Swift_Mime_HeaderSet;

/**
 *
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.1.0
 */
class DBFactory {
	const DB_MYSQL = 'mysql';
	const DB_POSTGRESQL = 'postgresql';

	private static $settings = null;

	/**
	 *
	 * @param string $dbType
	 * @param DBConnect $dbConnect
	 * @return DB The DB object
	 * @throws DBException
	 */
	public static function getNewInstance($dbType, DBConnect $dbConnect) {
		if(!extension_loaded('PDO'))
			throw new DBException('PDO extension is not available', 101);

		$instance = null;

		switch($dbType) {
			case 'mysql':
				$instance = new DBMySQL($dbConnect);
				break;
			case 'postgresql':
				// not implemented yet
				break;
			default:
				break;
		}

		if($instance !== null)
			return $instance;

		throw new DBException('Unknown DB type: ' . $dbType, 102);
	}
}

/* EOF */