<?php
namespace ch\timesplinter\logger;

/**
 * a logger that logs messages into a specified file
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
 */
class FileLogger extends Logger {

	private $fp;
	private $pattern;
	private $logfilePath;
	private $dtFormat;

	public function __construct($classContext, $logfilePath, $maxfilesize = 81920) {
		parent::__construt($classContext);

		$this->pattern = '%TIMESTAMP% - %MESSAGE%';
		$this->logfilePath = str_ireplace(array(Logger::PATTERN_CLASS), array($this->classContext), $logfilePath);

		if($maxfilesize > 0) {
			if(file_exists($this->logfilePath) && @filesize($this->logfilePath) > $maxfilesize)
				rename($this->logfilePath, $this->logfilePath . '.' . date('YmdHis'));
		}

		$filePathParts = explode('/', $this->logfilePath);
		array_pop($filePathParts);
		$filePath = implode('/', $filePathParts);
		
		if(is_dir($filePath) === false)
			mkdir($filePath, 0, true);
		
		$this->fp = fopen($this->logfilePath, 'a');
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
			Logger::PATTERN_CLASS => $this->classContext
			, Logger::PATTERN_LEVEL => str_pad($level, 5, ' ', STR_PAD_RIGHT)
			, Logger::PATTERN_MESSAGE => $msg
			, Logger::PATTERN_TIMESTAMP => date($this->dtFormat, $mtimeParts[1]) . ',' . substr($mtimeParts[0], 2) /* date('Y-m-d H:i:s,u') */
		);

		if($this->fp !== false)
			fwrite($this->fp, str_ireplace(array_keys($repl), $repl, $this->pattern) . "\r\n");
	}

	public function __destruct() {
		if($this->fp !== false)
			fclose($this->fp);
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