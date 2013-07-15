<?php

namespace ch\timesplinter\core;

use ch\timesplinter\core\HttpException;

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2013, METANET AG
 * @version 1.0.0
 */
interface HandleHttpError {
	public function displayHttpError(HttpException $e);
}

/* EOF */