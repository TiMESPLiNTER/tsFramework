<?php
namespace ch\timesplinter\logger;

use \Exception;

/**
 * the basic logger class
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.0.0
 */
abstract class Logger {
	const TYPE_FILE = 'file';
	const TYPE_STDOUT = 'stdout';
	const TYPE_DB = 'db';

	const LEVEL_FATAL = 'fatal';
	const LEVEL_ERROR = 'error';
	const LEVEL_WARN = 'warn';
	const LEVEL_INFO = 'info';
	const LEVEL_DEBUG = 'debug';

	const PATTERN_TIMESTAMP = '%TIMESTAMP%';
	const PATTERN_LEVEL = '%LEVEL%';
	const PATTERN_MESSAGE = '%MESSAGE%';
	const PATTERN_CLASS = '%CLASS%';
    const PATTERN_CLASSNAME = '%CLASSNAME%';

	protected $classContext;
	protected $loglevels;

	protected $mailLoglevels;
	protected $mailAddress;

	public function __construt($classContext, $loglevels) {
		$this->classContext = (is_object($classContext)) ? get_class($classContext) : $classContext;
        $this->loglevels = $loglevels;

		$this->mailAddress = null;
		$this->mailLoglevels = null;
	}

	private function checkLevel($levels, $msgLevel) {
		if(strpos($levels, $msgLevel) !== false)
			return true;

		return false;
	}

	protected abstract function writeMessage($level, $msg, $vars = null);

	private function mailMessage($level, $msg) {
		if($this->mailAddress === null || $this->mailLoglevels === null)
			return;

		if($this->checkLevel($this->mailLoglevels, $level) === false)
			return;

		$headers = array(
			'Subject: An error occured on ' . $_SERVER['SERVER_NAME'],
			'From: error@' . $_SERVER['SERVER_NAME']
		);

		error_log('[' . $level . '] ' . $msg, 1,
			$this->mailAddress,
			implode(PHP_EOL, $headers)
		);
	}

	/**
	 * Logs an error with optional exception
	 * @param string $msg
	 * @param Exception $e
	 */
	public function error($msg, Exception $e = null) {
		if($e !== null) {
			$msg .= PHP_EOL . get_class($e) . ': (' . $e->getCode() . ') "' . $e->getMessage() . '"' . PHP_EOL;
			$msg .= 'thrown in file: ' . $e->getFile() . ' (Line: ' . $e->getLine() . ')' . PHP_EOL . PHP_EOL;
			$msg .= $e->getTraceAsString();
		}

		$msg .= PHP_EOL . PHP_EOL . '$_SERVER = ' . print_r($_SERVER, true);
		$msg .= PHP_EOL . PHP_EOL . '$_GET = ' . print_r($_GET, true);
		$msg .= PHP_EOL . PHP_EOL . '$_POST = ' . print_r($_POST, true);
		$msg .= PHP_EOL . PHP_EOL . '$_FILES = ' . print_r($_FILES, true);
		$msg .= PHP_EOL . PHP_EOL . '$_COOKIE = ' . print_r($_COOKIE, true);

		$this->mailMessage(self::LEVEL_ERROR, $msg);

		if($this->checkLevel($this->loglevels, self::LEVEL_ERROR) !== true)
			return;

		$this->writeMessage(self::LEVEL_ERROR, $msg);
	}

	/**
	 * Logs a warning
	 * @param type $msg
	 */
	public function warn($msg) {
		$this->mailMessage(self::LEVEL_WARN, $msg);

		if($this->checkLevel($this->loglevels, self::LEVEL_WARN) !== true)
			return;

		$this->writeMessage(self::LEVEL_WARN, $msg);
	}

	/**
	 * Logs an information
	 * @param string $msg
	 * @return type
	 */
	public function info($msg) {
		$this->mailMessage(self::LEVEL_INFO, $msg);

		if($this->checkLevel($this->loglevels, self::LEVEL_INFO) !== true)
			return;

		$this->writeMessage(self::LEVEL_INFO, $msg);
	}

	/**
	 * Logs a debug message
	 * @param string $msg
	 * @param array $vars
	 */
	public function debug($msg, $vars = array()) {
		$this->mailMessage(self::LEVEL_DEBUG, $msg);

		if($this->checkLevel($this->loglevels, self::LEVEL_DEBUG) !== true)
			return;

		if(!is_array($vars))
			$vars = array($vars);

		$varDump = '';
		$varCount = count($vars);

		if($varCount > 0) {
			$vars = array_values($vars);
			$bt = debug_backtrace();

			$content = file($bt[0]['file']);
			$line = $content[$bt[0]['line'] - 1];

			preg_match('/debug\((.+?),array\((.+)\)\)/', $line, $matches);

			$varNames = null;
			if(count($matches) > 0)
				$varNames = explode(',', $matches[2]);

			for($i = 0; $i < $varCount; $i++) {
				$printedVar = trim(var_export($vars[$i], true));
				$varDump .= "\r\n\t" . (($varNames !== null) ? trim($varNames[$i]) . '=' : '') . str_replace("\n", "\r\n\t", $printedVar);
			}
		}

		$this->writeMessage(self::LEVEL_DEBUG, $msg . $varDump);
	}

	public function getLoglevels() {
		return $this->loglevels;
	}

	public function setLoglevels($loglevels) {
		$this->loglevels = $loglevels;
	}

	public function setMailLoglevels($mailLoglevels) {
		$this->mailLoglevels = $mailLoglevels;
	}

	public function setMailAddress($mailAddress) {
		$this->mailAddress = $mailAddress;
	}

	public static function var_name($var) {
		$trace = debug_backtrace();
		$vLine = file(__FILE__);
		$fLine = $vLine[$trace[0]['line'] - 1];
		preg_match('/\\$(\\w+)/', $fLine, $match);

		return isset($match[1]) ? $match[1] : null;
	}

}

/* EOF */