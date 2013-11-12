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
		if(isset($this->core->getSettings()->errorhandling->controller) === true) {
			$controller = FrameworkUtils::stringToClassName($this->core->getSettings()->errorhandling->controller);

			$pc = new $controller->className($this->core, $this->core->getHttpRequest(), new Route());

			$httpResponse = call_user_func(array($pc, $controller->methodName), $e);
		} else {
			$content = null;

			$environment = $this->core->getCurrentDomain()->environment;
			$httpErrroCode = ($e instanceof HttpException)?$e->getCode():500;
			$exceptionStr = null;

			if($this->core->getSettings()->core->environments->$environment->debug === true) {
				$exceptionStr = "\n<pre>";

				$exceptionStr .= '<b>Uncaught exception' . "\n" . '==================</b>' . "\n";
				$exceptionStr .= 'Type: ' . get_class($e) . "\n";
				$exceptionStr .= 'Message: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')' . "\n";
				$exceptionStr .= 'Thrown in: ' . $e->getFile() . ' (Line: ' . $e->getLine() . ")\n\n";

				$exceptionStr .= $e->getTraceAsString();

				$exceptionStr .= '</pre>';
			}

			$errorStr = $httpErrroCode . ' ' . HttpResponse::getHttpStatusString($httpErrroCode);
			$content = "<!doctype html>\n<html>\n<head>\n<title>" . $errorStr . "</title>\n</head>\n<body>\n<h1>" . $errorStr . "</h1>" . $exceptionStr . "\n</body>\n</html>";

			$httpResponse = new HttpResponse($httpErrroCode, $content);
		}

		$httpResponse->send();

		exit;
	}
}

/* EOF */