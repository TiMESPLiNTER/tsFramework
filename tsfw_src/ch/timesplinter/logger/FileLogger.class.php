<?php
namespace ch\timesplinter\logger;

use ch\timesplinter\common\StringUtils;

/**
 * a logger that logs messages into a specified file
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.0.0
 */
class FileLogger extends Logger {

	private $pattern;
	private $logfilePath;
	private $dtFormat;

	public function __construct($classContext, $loglevels, $logfilePath, $maxfilesize = 0) {
		parent::__construt($classContext, $loglevels);

		$this->pattern = '%TIMESTAMP% - %MESSAGE%';

        $repl = array(
            Logger::PATTERN_CLASS => $this->classContext,
            Logger::PATTERN_CLASSNAME => StringUtils::afterLast($this->classContext, '\\'),
            Logger::PATTERN_LEVEL => $loglevels
        );

		$this->logfilePath = str_ireplace(array_keys($repl), $repl, $logfilePath);

		if($maxfilesize > 0) {
			if(file_exists($this->logfilePath) && @filesize($this->logfilePath) > $maxfilesize)
				rename($this->logfilePath, $this->logfilePath . '.' . date('YmdHis'));
		}

		$this->dtFormat = 'Y-m-d H:i:s';
	}

	/**
	 * Writes the message with the given pattern into the defined log-file
	 * @param type $level
	 * @param type $msg
	 * @param type $vars
	 */
	public function writeMessage($level, $msg, $vars = null) {
		// Because of date('u')-PHP-bug (always 00000)
		$mtimeParts = explode(' ', microtime());

		$repl = array(
			Logger::PATTERN_CLASS => $this->classContext,
            Logger::PATTERN_CLASSNAME => StringUtils::afterLast($this->classContext, '\\'),
			Logger::PATTERN_LEVEL => str_pad($level, 5, ' ', STR_PAD_RIGHT),
			Logger::PATTERN_MESSAGE => $msg,
			Logger::PATTERN_TIMESTAMP => date($this->dtFormat, $mtimeParts[1]) . ',' . substr($mtimeParts[0], 2) /* date('Y-m-d H:i:s,u') */
		);

		error_log(str_ireplace(array_keys($repl), $repl, $this->pattern) . "\r\n", 3, $this->logfilePath);
	}

	public function getPattern() {
		return $this->pattern;
	}

	public function setPattern($pattern) {
		$this->pattern = $pattern;
	}

	public function getLogfilePath() {
		return $this->logfilePath;
	}

	public function setLogfilePath($logfilePath) {
		$this->logfilePath = $logfilePath;
	}

}

?>