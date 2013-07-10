<?php

/**
 * This logger sends the logged message to a list of other loggers. They can use the log message or throw it away.
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, Pascal Muenst
 * @version 1.0.0
 */

namespace ch\timesplinter\logger;

use ch\timesplinter\logger\Logger;

class BroadcastLogger extends Logger {
	private $loggers;

	public function  __construct($classContext, $loggers) {
		parent::__construt($classContext, 'info,warn,error,debug');

		$this->loggers = $loggers;
	}

	/**
	 * Logs an error with optional exception
	 * @param string $msg
	 * @param \Exception $e
	 */
	public function error($msg, \Exception $e = null) {
		foreach($this->loggers as $logger)
			$logger->error($msg, $e);
	}

	/**
	 * Logs a warning
	 * @param type $msg
	 */
	public function warn($msg) {
		foreach($this->loggers as $logger)
			$logger->warn($msg);
	}

	/**
	 * Logs an information
	 * @param string $msg
	 * @return type
	 */
	public function info($msg) {
		foreach($this->loggers as $logger)
			$logger->info($msg);
	}

	/**
	 * Logs a debug message
	 * @param string $msg
	 * @param array $vars
	 */
	public function debug($msg, $vars = array()) {
		foreach($this->loggers as $logger)
			$logger->debug($msg, $vars);
	}

	protected function writeMessage($level, $msg, $vars = null) {
		// Never called
	}
}

/* EOF */