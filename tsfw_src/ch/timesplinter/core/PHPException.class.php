<?php
namespace ch\timesplinter\core;

/**
 * Description of PHPException
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.0.0
 */
class PHPException extends FrameworkException {
	public function __construct($number, $message, $file, $line) {
		parent::__construct($message, $number);

		$this->file = $file;
		$this->line = $line;
	}

	public function handleException(Core $core, HttpRequest $httpRequest) {
		$phpErrors = array(
			 E_ERROR => array('name' => 'E_ERROR', 'title' => 'fatal run-time error')
			,E_WARNING => array('name' => 'E_WARNING', 'title' => 'run-time warning')
			,E_PARSE => array('name' => 'E_PARSE', 'title' => 'compile-time parse error')
			,E_NOTICE => array('name' => 'E_NOTICE', 'title' => 'run-time notice')
			,E_CORE_ERROR => array('name' => 'E_CORE_ERROR', 'title' => 'fatal error (PHP initial startup)')
			,E_CORE_WARNING => array('name' => 'E_CORE_WARNING', 'title' => 'warning (PHP initial startup)')
			,E_COMPILE_WARNING => array('name' => 'E_COMPILE_WARNING', 'title' => 'compile-time warning')
			,E_USER_ERROR => array('name' => 'E_USER_ERROR', 'title' => 'user-generated error with <a href="http://php.net/manual/en/function.trigger-error.php">trigger_error()</a>')
			,E_USER_WARNING => array('name' => 'E_USER_WARNING', 'title' => 'user-generated warning with <a href="http://php.net/manual/en/function.trigger-error.php">trigger_error()</a>')
			,E_USER_NOTICE => array('name' => 'E_USER_NOTICE', 'title' => 'user-generated notice with <a href="http://php.net/manual/en/function.trigger-error.php">trigger_error()</a>')
			,E_STRICT => array('name' => 'E_STRICT', 'title' => 'strict-standard violation')
			,E_RECOVERABLE_ERROR => array('name' => 'E_RECOVERABLE_ERROR', 'title' => 'catchable fatal error')
			,E_DEPRECATED => array('name' => 'E_DEPRECATED', 'title' => 'run-time notice about deprecated code')
			,E_USER_DEPRECATED => array('name' => 'E_USER_DEPRECATED', 'title' => 'user-generated notice about deprecated code with <a href="http://php.net/manual/en/function.trigger-error.php">trigger_error()</a>')
		);
		
		$errorTypeStr = isset($phpErrors[$this->code])?$phpErrors[$this->code]['name'] . ': ' . $phpErrors[$this->code]['title']:'unknown';
			
				
		$title = 'PHP ' . $errorTypeStr . ' (' . $this->code . ')';
				
		echo '<pre>';
		echo '<b>' , $title , "</b>\n";
		echo str_pad('', strlen($title), '-') , "\n";
		echo 'Message: ' , $this->message , "\n";
		echo 'File:    ' , $this->file , "\n";
		echo 'Line:    ' , $this->line , "\n\n";
		
		echo $this->getTraceAsString();
		
		echo '</pre>';
	}
}

/* EOF */