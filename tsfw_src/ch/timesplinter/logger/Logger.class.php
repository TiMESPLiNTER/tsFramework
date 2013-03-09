<?php
namespace ch\timesplinter\logger;

/**
 * the basic logger class
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
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

	protected $classContext;
	protected $loglevels;

	public function __construt($classContext) {
		$this->classContext = (is_object($classContext)) ? get_class($classContext) : $classContext;
	}

	private function checkLevel($level) {
		if(strpos($this->loglevels, $level) !== false)
			return true;

		return false;
	}

	abstract function writeMessage($level, $msg, $vars = null);

	/**
	 * Logs an error with optional exception
	 * @param string $msg
	 * @param Exception $e
	 * @param array $vars
	 */
	public function error($msg, Exception $e = null) {
		if(self::checkLevel(self::LEVEL_ERROR) !== true)
			return;

		if($e !== null)
			$msg .= "\r\n\tException: (" . $e->getCode() . ') ' . $e->getMessage();

		$this->writeMessage(self::LEVEL_ERROR, $msg);
	}

	/**
	 * Logs a warning
	 * @param type $msg
	 * @param type $vars
	 */
	public function warn($msg) {
		if(self::checkLevel(self::LEVEL_WARN) !== true)
			return;

		$this->writeMessage(self::LEVEL_WARN, $msg);
	}

	/**
	 * Logs an information
	 * @param type $msg
	 * @return type
	 */
	public function info($msg) {
		if(self::checkLevel(self::LEVEL_INFO) !== true)
			return;

		$this->writeMessage(self::LEVEL_INFO, $msg);
	}

	/**
	 * Logs a debug message
	 * @param string $msg
	 * @param array $vars
	 */
	public function debug($msg, $vars = array()) {
		if(self::checkLevel(self::LEVEL_DEBUG) !== true)
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

	public static function var_name($var) {
		$trace = debug_backtrace();
		$vLine = file(__FILE__);
		$fLine = $vLine[$trace[0]['line'] - 1];
		preg_match('/\\$(\\w+)/', $fLine, $match);

		return isset($match[1]) ? $match[1] : null;
	}

}

?>