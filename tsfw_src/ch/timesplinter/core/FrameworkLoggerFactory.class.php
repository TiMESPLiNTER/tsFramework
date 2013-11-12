<?php
/**
 * @author Pascal Muenst
 * @copyright Copyright (c) 2013, Pascal Muenst
 * @version 1.0.0
 */

namespace ch\timesplinter\core;

use ch\timesplinter\logger\BroadcastLogger;
use ch\timesplinter\logger\LoggerFactory;

class FrameworkLoggerFactory {
	private static $loggers;
	private static $environmet;
	private static $fwRoot;
	private static $siteRoot;

	/**
	 * @param mixed $classContext The context the logger should be for
	 * @param string|null $name The name of the logger or null instead
	 * @return BroadcastLogger The logger instance
	 */
	public static function getLogger($classContext, $name = null) {
		if(self::$loggers === null) {
			$settings = new Settings(
				self::$siteRoot . 'settings' . DIRECTORY_SEPARATOR,
				self::$siteRoot . 'cache' . DIRECTORY_SEPARATOR,
				array(
				'fw_dir' => self::$fwRoot,
				'site_root' => self::$siteRoot
				)
			);

			self::$loggers = $settings->loggers->{self::$environmet};
		}

		$createdLoggers = array();

		foreach(self::$loggers as $loggerName => $loggerSettings) {
			$createdLoggers[$loggerName] = LoggerFactory::getLogger($loggerSettings, $classContext);
		}

		return new BroadcastLogger($classContext, ($name === null)?$createdLoggers:$createdLoggers[$name]);
	}

	public static function setDefaults($environment, $fwRoot, $siteRoot) {
		self::$environmet = $environment;
		self::$fwRoot = $fwRoot;
		self::$siteRoot = $siteRoot;
	}
}

/* EOF */