<?php
namespace ch\timesplinter\core;

/**
 * Description of FrameworkException
 *
 * @author pascal91
 */
abstract class FrameworkException extends \Exception {
	public function __construct($message, $code) {
		parent::__construct($message, $code);
	}
	
	//put your code here
	abstract public function handleException();
}

?>
