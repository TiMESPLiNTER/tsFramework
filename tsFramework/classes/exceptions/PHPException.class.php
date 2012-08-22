<?php

/**
 * Description of PHPException
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Metanet AG
 * @version 1.0
 */
class PHPException extends Exception {
	private $errorFile;
	private $errorLine;
	
	public function __construct($errorNumber,$errorMessage,$errorFile,$errorLine) {
		parent::__construct($errorMessage, $errorNumber);
		
		$this->errorFile = $errorFile;
		$this->errorLine = $errorLine;
	}
}

?>
