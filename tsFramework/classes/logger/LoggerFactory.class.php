<?php

/**
 * logger factory
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class LoggerFactory {

	private static $settings;

	public static function getLoggerByName($loggerName, $classContext) {
		if(self::$settings === null) {
			$settings = Settings::getInstance();
			self::$settings = $settings->load('loggers', __CLASS__);
		}
		if(!isset(self::$settings[$loggerName])) {
			echo 'no settings specified for logger ' . $loggerName . ' in the following context:';
			var_dump($classContext);
			exit;
		}
		$loggerOpts = self::$settings[$loggerName];

		$loggerInstance = null;

		switch($loggerOpts['type']) {
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

	/**
	 * returns a logger instance for the current environment of the FrameWork
	 * @param stdClass|string $classContext The class to log for ($this)
	 * @return Logger a instance of the logger to log to
	 */
	public static function getEnvLogger($classContext) {
		$environmentHandler = EnvironmentHandler::getInstance();
		return self::getLoggerByName($environmentHandler->getEnvironment(), $classContext);
	}

}

?>
