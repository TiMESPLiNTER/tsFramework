<?php
namespace ch\timesplinter\core;

/**
 * Description of ErrorHandler
 *
 * @author pascal91
 */
class ErrorHandler {
	private $core;
	
	public function __construct() {
		
	}
	
	public function register() {
		set_error_handler(array($this,'handlePHPError'));  
		set_exception_handler(array($this,'handleException'));
	}
	
	public function displayHttpError($errorCode, $httpRequest) {
		$errorStr = null;
		
		switch($errorCode) {
			case 404:
				$errorStr = 'We\'re sorry but we couldn\'t find this page on our server.';
				break;
			case 500:
				$errorStr = 'An internal server error occured. It\'s our fault! We apologize!';
				break;
			default:
				$errorStr = 'An unknown error occured. We\'re so sorry about that!';
				break;
		}
		
		$pc = new StaticPageController($this->core, $httpRequest, new Route());
		
		return $pc->getErrorPage($errorCode, $errorStr);
	}
	
	/**
	 * 
	 * @param type $error_number
	 * @param type $error
	 * @param type $error_file
	 * @param type $error_line
	 * @throws PHPException
	 */
	public function handlePHPError($error_number,$error,$error_file,$error_line) {
		throw new PHPException($error_number,$error,$error_file,$error_line);
	}
	
	/**
	 * Default stub for print an exception
	 * @param \Exception $e
	 */
	public function handleException(\Exception $e) {
		ob_clean();
		
		header('HTTP/1.1 500 Server error');
		
		if($e instanceof FrameworkException) {
			$e->handleException();
		} else {
			echo '<pre>'; 
			
			echo '<b>Uncaught exception' , "\n" , '==================</b>' , "\n";
			echo 'Type: ' , get_class($e) , "\n";
			echo 'Message: ' , $e->getMessage() , ' (Code: ' , $e->getCode() , ')' , "\n";
			echo 'Thrown in: ' , $e->getFile(), ' (Line: ' , $e->getLine() , ")\n\n";

			echo $e->getTraceAsString();
			
			echo'</pre>';
		}
		
		ob_end_flush();
		exit;
	}
	
	public function setCore(Core $core) {
		$this->core = $core;
	}
}

/* EOF */