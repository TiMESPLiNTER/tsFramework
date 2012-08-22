<?php

/**
 * Description of ErrorHandler
 *
 * @author pascal91
 */
class ErrorHandler {
	public function __construct() {
		
	}
	
	public function register() {
		set_error_handler(array($this,'handlePHPError'));  
		set_exception_handler(array($this,'handleException'));
	}
	
	public static function displayHttpError($errorCode) {
		$errorStr = null;
		
		switch($errorCode) {
			case 404:
				$errorStr = '404 Not found';
				break;
			case 500:
				$errorStr = '500 Server error';
				break;
			default:
				throw new Exception('Illegal http error code: ' . $errorCode);
				break;
		}
		
		// Cause of fucking buggy ob_gzhandler
		ob_end_clean();
		ob_start('ob_gzhandler');
		
		header('HTTP/1.1 ' . $errorStr);
		header('Content-Type: text/html; charset=UTF-8');
		echo 'Error: ' , $errorStr;
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
	
	public function handleException(Exception $e) {
		ob_clean();
		
		header('HTTP/1.1 500 Server error');
		
		if($e instanceof FrameworkException) {
			$e->handleException();
		} else {
			echo '<pre>'; 

			echo 'Type: ' , get_class($e) , "\n";
			echo 'Message: ' , $e->getMessage() , ' (Code: ' , $e->getCode() , ')' , "\n\n";

			echo $e->getTraceAsString();
			
			echo'</pre>';
		}
		
		ob_end_flush();
		exit;
	}
}

?>
