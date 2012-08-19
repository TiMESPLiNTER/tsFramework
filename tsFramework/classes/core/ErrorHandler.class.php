<?php

/**
 * Description of ErrorHandler
 *
 * @author pascal91
 */
class ErrorHandler {
	public function displayHttpError($errorCode) {
		$errorStr = null;
		
		switch($errorCode) {
			case 404:
				$errorStr = '404 Not found';
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
}

?>
