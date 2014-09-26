<?php

namespace ch\timesplinter\core;

use \Exception;

/**
 * Description of ErrorHandler
 * @package ch\timesplinter\core
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER
 */
class ErrorHandler {
	private $core;
	
	public function __construct(Core $core) {
		$this->core = $core;
	}
	
	public function register() {
		set_error_handler(array($this, 'handlePHPError'));
		set_exception_handler(array($this, 'handleException'));
		register_shutdown_function(array($this, 'handleFatalError'));
	}
	
	/**
	 * Coverts thrown PHP error into an exception
	 * @param int $error_number
	 * @param string $error
	 * @param string $error_file
	 * @param int $error_line
	 * @throws PHPException
	 */
	public function handlePHPError($error_number, $error, $error_file, $error_line)
	{
		// error was suppressed with the @-operator
		if(0 === error_reporting())
			return false;
		
		throw new PHPException($error_number, $error, $error_file, $error_line);
	}

	public function handleFatalError() {
		$error = error_get_last();

		if ($error['type'] !== E_ERROR)
			return;

		throw new PHPException($error['type'], $error['message'], $error['file'], $error['line']);
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
			$httpErrorCode = ($e instanceof HttpException)?$e->getCode():500;
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

			$errorStr = $httpErrorCode . ' ' . HttpResponse::getHttpStatusString($httpErrorCode);
			$content = "<!doctype html>\n<html>\n<head>\n<title>" . $errorStr . "</title>\n</head>\n<body>\n<h1>" . $errorStr . "</h1>" . $exceptionStr . "\n</body>\n</html>";

			$httpResponse = new HttpResponse($httpErrorCode, $content);
		}

		$httpResponse->send();

		exit;
	}
}

/* EOF */