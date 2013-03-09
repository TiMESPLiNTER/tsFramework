<?php
namespace ch\timesplinter\template;

use \Exception;

/**
 * TemplateEngineException
 *
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
 */
class TemplateEngineException extends Exception {
	public function __construct($message, $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

?>