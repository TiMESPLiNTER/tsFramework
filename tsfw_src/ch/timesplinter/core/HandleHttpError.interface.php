<?php

namespace ch\timesplinter\core;

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2013, METANET AG
 * @version 1.0.0
 */
interface HandleHttpError {
	/**
	 * @param \Exception $e
	 * @param int $httpStatusCode
	 * @return HttpResponse
	 */
	public function displayHttpError(\Exception $e, $httpStatusCode);
}

/* EOF */