<?php

/**
 * TemplateEngineException
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class TemplateEngineException extends Exception {

	public function __construct($message) {
		echo $message;
		exit;
	}

}

?>