<?php

/**
 *
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class DBFactory {

	private static $settings = null;

	/**
	 *
	 * @param type $dbType
	 * @param DBConnect $dbConnect
	 * @return DB The DB object
	 */
	public static function getNewInstance($dbType, DBConnect $dbConnect) {
		if(!extension_loaded('PDO'))
			throw new DBException('PDO extension is not available', 101);

		$instance = null;

		switch($dbType) {
			case DB::TYPE_MYSQL:
				$instance = new DBMySQL($dbConnect);
				break;
			case DB::TYPE_POSTGRESQL:
				$instance = new DBPostgreSQL($dbConnect);
				break;
			default:
				break;
		}

		if($instance !== null)
			return $instance;

		throw new DBException('Unknown DB type: ' . $dbType);
	}

	/**
	 * Returns a DB instance for the current environement of the FrameWork
	 * @param stdClass|string $classContext The class to log for ($this)
	 * @return Logger A instance of the logger to log to
	 */
	public static function getEnvInstance() {
		$currentEnv = EnvironmentHandler::getInstance();

		/** @var Settings */
		if(self::$settings === null) {
			$settings = Settings::getInstance();
			self::$settings = $settings->load('db', __CLASS__);
		}

		//var_dump($currentEnv);

		if(!array_key_exists($currentEnv->getEnvironment(), self::$settings))
			throw new FrameworkException('No database connection defined for this environment');

		$dbConnOpts = self::$settings[$currentEnv->getEnvironment()];

		$dbConnect = new DBConnect($dbConnOpts['host'], $dbConnOpts['database'], $dbConnOpts['username'], $dbConnOpts['password']);

		return self::getNewInstance($dbConnOpts['type'], $dbConnect);
	}

}

?>
