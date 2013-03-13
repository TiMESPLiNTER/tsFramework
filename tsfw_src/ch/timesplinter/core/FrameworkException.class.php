<?php
namespace ch\timesplinter\core;

/**
 * Description of FrameworkException
 *
 * @author pascal91
 */
abstract class FrameworkException extends \Exception {
	public function __construct($message = '', $code = 0, Exception $previous = null) {
		parent::__construct($message, $code);
	}
	
	//put your code here
	public function handleException() {
        $exceptionClassName = get_class($this);
        $grammar = (strtolower(substr($exceptionClassName,0,1)) === 'e')?'n':null;

        echo '<pre>';
        echo '<b>A' , $grammar , ' ' , $exceptionClassName, ' occured</b>' , "\n";
        echo 'Message:   ' , $this->getMessage() , '(Code: ' , $this->getCode() , ')' , "\n";
        echo 'Thrown in: ' , $this->getFile() , ' (Line: ' , $this->getLine() , ")\n\n";

        echo $this->getTraceAsString();
        echo '</pre>';
    }
}

/* EOF */