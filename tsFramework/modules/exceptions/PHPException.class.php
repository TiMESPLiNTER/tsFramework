<?php

/**
 * Description of PHPException
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Metanet AG
 * @version 1.0
 */
class PHPException extends FrameworkException {
	private $errorFile;
	private $errorLine;
	
	public function __construct($errorNumber,$errorMessage,$errorFile,$errorLine) {
		parent::__construct($errorMessage, $errorNumber);
		
		$this->errorFile = $errorFile;
		$this->errorLine = $errorLine;
	}

	public function handleException() {
		$errorTypeStr = null;
		
		switch($this->code) {
			case E_NOTICE:
				$errorTypeStr = 'notice';
				break;
			case E_ERROR:
				$errorTypeStr = 'error';
				break;
			case E_WARNING:
				$errorTypeStr = 'warning';
				break;
			default:
				$errorTypeStr = 'unknown (' . $this->getCode() . ')';
				break;
		}
				
		$title = 'PHP ' . $errorTypeStr . '';
				
		echo '<pre>';
		echo '<b>' , $title , "</b>\n";
		echo str_pad('', strlen($title), '-') , "\n";
		echo 'Message: ' , $this->message , "\n";
		echo 'File:    ' , $this->errorFile , "\n";
		echo 'Line:    ' , $this->errorLine , "\n\n";
		
		echo $this->getTraceAsString();
		
		echo '</pre>';
	}
}

?>
