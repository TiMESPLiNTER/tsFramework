<?php

namespace ch\timesplinter\logger;

use \stdClass;

/**
 * logger factory
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.0.0
 */
class LoggerFactory {
	/**
	 * 
	 * @param stdClass $settings
	 * @param string $classContext
	 * @return \Logger
	 */
	public static function getLogger(stdClass $loggerSettings, $classContext) {
		$loggerInstance = null;

		switch ($loggerSettings->type) {
			case Logger::TYPE_FILE:
				$loggerInstance = new FileLogger(
					$classContext,
					$loggerSettings->log_level,
					$loggerSettings->logfilepath,
					isset($loggerSettings->maxfilesize) ? $loggerSettings->maxfilesize : 0
				);
				$loggerInstance->setPattern($loggerSettings->pattern);
				$loggerInstance->setLogfilePath($loggerSettings->logfilepath);
				break;

			case Logger::TYPE_STDOUT:
				$loggerInstance = new StdOutLogger($classContext, $loggerSettings->log_level);
				$loggerInstance->setPattern($loggerSettings->pattern);
				break;

			case Logger::TYPE_DB:
				break;

			default:
				throw new \Exception('Unknown logger type: ' . $loggerSettings->type);
				break;
		}

		if(
			isset($loggerSettings->email_address) === true &&
			isset($loggerSettings->email_level) === true &&
			strlen($loggerSettings->email_level) > 0
		) {
			$loggerInstance->setMailAddress($loggerSettings->email_address);
			$loggerInstance->setMailLoglevels($loggerSettings->email_level);
		}

		return $loggerInstance;
	}
}

/* EOF */