<?php

namespace ch\timesplinter\core;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 */
class ErrorHandler
{
	private $core;
	
	public function __construct(Core $core)
	{
		$this->core = $core;
	}
	
	public function register()
	{
		set_error_handler(array($this, 'handlePHPError'));
		set_exception_handler(array($this, 'handleException'));
	}

	/**
	 * Catches all the PHP errors and convert them into a PHP exception
	 *
	 * @param int $error_number
	 * @param string $error
	 * @param string $error_file
	 * @param int $error_line
	 *
	 * @throws PHPException
	 */
	public function handlePHPError($error_number, $error, $error_file, $error_line)
	{
		// respect the current error_reporting setting
		if ((error_reporting() & $error_number) === false)
			return;
		
		throw new PHPException($error_number, $error, $error_file, $error_line);
	}
	
	/**
	 * Default stub for print an exception
	 *
	 * @param \Exception $e
	 */
	public function handleException(\Exception $e)
	{
        $environment = $this->core->getCurrentDomain()->environment;
		
		if(isset($this->core->getSettings()->errorhandling->controller->$environment) === true) {
			$controller = FrameworkUtils::stringToClassName($this->core->getSettings()->errorhandling->controller->$environment);

			$pc = new $controller->className($this->core, $this->core->getHttpRequest(), new Route());

			$httpResponse = call_user_func(array($pc, $controller->methodName), $e);
		} else {
			$content = null;

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