<?php
/**
 * Description of DB
 *
 * @author Pascal Münst (Actra AG)
 * @copyright 2011
 * @version 12.3
 */
class PHibernateFactory {
	private static $settings = null;
	
	private static $instances = array();
	
	/**
	 * Returns a PHibernate Framework Instance
	 * @param DB $classContext The class to log for ($this)
	 * @return PHibernate A instance of the logger to log to 
	 */
	public static function getEnvInstance(DB $db) {
		$currentEnv = EnvironmentHandler::getInstance();
		
		/** @var Settings */
		if(self::$settings === null) {
			$settings = Settings::getInstance();
			self::$settings = $settings->load('phibernate',__CLASS__);
		}
		
		//var_dump($currentEnv);
		
		if(!array_key_exists($currentEnv->getEnvironment(), self::$settings))
			throw new FrameworkException('No phibernate settings defined for this environment');
		
		$phiberOpts = self::$settings[$currentEnv->getEnvironment()];
		
		$dbKey = $phiberOpts['database'];
		
		if(array_key_exists($dbKey, self::$instances) === false) {
			self::$instances[$dbKey] = new PHibernate($dbKey, $db, $phiberOpts['schemaFilepath'], $phiberOpts['cacheDir']);
		}
		
		return self::$instances[$dbKey];
	}
}

?>
