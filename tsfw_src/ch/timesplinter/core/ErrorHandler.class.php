<?php
namespace ch\timesplinter\core;

use ch\timesplinter\core\Core;
use ch\timesplinter\controller\StaticPageController;
use \Exception;

/**
 * Description of ErrorHandler
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER
 * @version 1.0.0
 */
class ErrorHandler {
	private $core;
	
	public function __construct(Core $core) {
		$this->core = $core;
	}
	
	public function register() {
		set_error_handler(array($this, 'handlePHPError'));
		set_exception_handler(array($this, 'handleException'));
	}
	
	public function displayHttpError($errorCode, $httpRequest, $errorStr = null) {
        $language = $this->core->getLocaleHandler()->getLanguage();
        $messages = $this->core->getSettings()->errorhandling->messages;

		if($errorStr !== null) {
	        if($language === false)
	            $language = 'en';

	        $errorStr = isset($messages->$errorCode)?$messages->$errorCode->$language:$messages->default->$language;
		}

		$errorControllerMethod = isset($this->core->getSettings()->errorhandling->controller->$errorCode)?$errorCode:'default';
		$errorHandlerController = $this->core->getSettings()->errorhandling->controller->$errorControllerMethod;

		$controller = FrameworkUtils::stringToClassName($errorHandlerController);

		$errorRoute = new Route();
		$errorRoute->id = 'error';

		$pc = new $controller->className($this->core, $httpRequest, $errorRoute);
		
		return $pc->{$controller->methodName}(array(
			'siteTitle' => 'Error ' . $errorCode,
			'error_code' => $errorCode,
			'error_msg' => $errorStr
		), $errorCode);
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
		throw new PHPException($error_number, $error, $error_file, $error_line);
	}
	
	/**
	 * Default stub for print an exception
	 * @param Exception $e
	 */
	public function handleException(Exception $e) {
		$content = null;
		
		if($e instanceof FrameworkException) {
			$content = $e->handleException();
		} else {
			$content = '<pre>';

			$content .= '<b>Uncaught exception' . "\n" . '==================</b>' . "\n";
			$content .= 'Type: ' . get_class($e) . "\n";
			$content .= 'Message: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')' . "\n";
			$content .= 'Thrown in: ' . $e->getFile() . ' (Line: ' . $e->getLine() . ")\n\n";

			$content .= $e->getTraceAsString();
			
			$content .= '</pre>';
		}
		
		$httpResponse = new HttpResponse(500, $content);
		$httpResponse->send();

		exit;
	}
}

/* EOF */