<?php

namespace ch\timesplinter\core;

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2013, METANET AG
 */
interface HandleHttpError
{
	/**
	 * @param HttpException $e
	 *
	 * @return HttpResponse
	 */
	public function displayHttpError(HttpException $e);
}

/* EOF */