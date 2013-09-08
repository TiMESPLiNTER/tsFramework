<?php
namespace ch\timesplinter\core;

use \Exception;

/**
 * Description of FrameworkException
 *
 * @author pascal91
 */
abstract class FrameworkException extends Exception {
	public function __construct($message = '', $code = 0, Exception $previous = null) {
		parent::__construct($message, $code);
	}
	
	//put your code here
	public function handleException(Core $core, HttpRequest $httpRequest) {
        $exceptionClassName = get_class($this);
        $grammar = (strtolower(substr($exceptionClassName,0,1)) === 'e')?'n':null;

        $content  = '<pre>';
		$content .= '<b>A' . $grammar . ' ' . $exceptionClassName . ' occured</b>' . "\n";
		$content .= 'Message:   ' . $this->getMessage() . '(Code: ' . $this->getCode() . ')' . "\n";
        $content .= 'Thrown in: ' . $this->getFile() . ' (Line: ' . $this->getLine() . ")\n\n";

        $content .=  $this->getTraceAsString();
        $content .=  '</pre>';

		return $content;
    }
}

/* EOF */