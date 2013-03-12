<?php
namespace ch\timesplinter\logger;

/**
 * logger factory
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
 */
class LoggerFactory {

	private static $settings;

	/**
	 * 
	 * @param type $loggerName
	 * @param type $classContext
	 * @return \Logger
	 */
	public static function getLoggerByName($loggerName, $classContext) {
		if (self::$settings === null) {
			//$settings = Settings::getInstance();
			self::$settings = array('dev' => array(
			'type' => Logger::TYPE_FILE,
			'logfilepath' => SITE_ROOT . 'logs/dev-log.log',
			'pattern' => '[%LEVEL%] %TIMESTAMP%@%CLASS% - %MESSAGE%',
			'log_level' => 'debug,info,warn,error,fatal',
			'email_level' => 'error,fatal',
			'email_address' => 'metanet.ch',
			'maxfilesize' => 0
			));
		}
		if (!isset(self::$settings[$loggerName])) {
			echo 'no settings specified for logger ' . $loggerName . ' in the following context:';
			var_dump($classContext);
			exit;
		}
		$loggerOpts = self::$settings[$loggerName];

		$loggerInstance = null;

		switch ($loggerOpts['type']) {
			case Logger::TYPE_FILE:
				$loggerInstance = new FileLogger($classContext, $loggerOpts['logfilepath'], (isset($loggerOpts['maxfilesize']) ? $loggerOpts['maxfilesize'] : 0));
				$loggerInstance->setPattern($loggerOpts['pattern']);
				$loggerInstance->setLogfilePath($loggerOpts['logfilepath']);
				$loggerInstance->setLoglevels($loggerOpts['log_level']);
				break;

			case Logger::TYPE_STDOUT:
				$loggerInstance = new StdOutLogger($classContext);
				$loggerInstance->setPattern($loggerOpts['pattern']);
				$loggerInstance->setLoglevels($loggerOpts['log_level']);
				break;

			case Logger::TYPE_DB:
				break;

			default:
				break;
		}
		return $loggerInstance;
	}
}

?>
