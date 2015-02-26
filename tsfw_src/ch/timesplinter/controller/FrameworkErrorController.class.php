<?php

namespace ch\timesplinter\controller;

use ch\timesplinter\core\HttpResponse;
use \Exception;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
abstract class FrameworkErrorController extends FrameworkController
{
	/**
	 * @param Exception $e The thrown exception
	 * @return HttpResponse
	 */
	public abstract function getExceptionResponse(Exception $e);
}

/* EOF */