<?php
namespace ch\timesplinter\logger;

/**
 * a logger that logs messages directly on the screen (browser)
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
 */
class StdOutLogger extends Logger {

	private $pattern;
	private $dtFormat;

	public function __construct($classContext) {
		ob_start();

		parent::__construt($classContext);

		$this->pattern = '%TIMESTAMP% - %MESSAGE%';
		$this->dtFormat = 'Y-m-d H:i:s';
	}

	public function writeMessage($level, $msg, $vars = null) {
		$lvCol = '#000';

		switch($level) {
			case Logger::LEVEL_ERROR:
			case Logger::LEVEL_FATAL:
				$lvCol = '#C00';
				break;
			case Logger::LEVEL_INFO:
				$lvCol = '#00C';
				break;
			case Logger::LEVEL_WARN:
				$lvCol = '#FFCC33';
				break;
			default:
				break;
		}

		// Because of date('u')-PHP-bug (always 00000)
		$mtimeParts = explode(' ', microtime());

		$repl = array(
			Logger::PATTERN_CLASS => $this->classContext
			, Logger::PATTERN_LEVEL => str_pad($level, 5, ' ', STR_PAD_RIGHT)
			, Logger::PATTERN_MESSAGE => $msg
			, Logger::PATTERN_TIMESTAMP => date($this->dtFormat, $mtimeParts[1]) . ',' . substr($mtimeParts[0], 2)/* date('Y-m-d H:i:s,u') */
		);

		echo '<pre style="color:' . $lvCol . '; font-size:12px; margin:0; line-height:1.5em;">' . str_ireplace(array_keys($repl), $repl, $this->pattern) . '</pre>', "\n";
		//ob_flush();
	}

	public function getPattern() {
		return $this->pattern;
	}

	public function setPattern($pattern) {
		$this->pattern = $pattern;
	}

}

?>