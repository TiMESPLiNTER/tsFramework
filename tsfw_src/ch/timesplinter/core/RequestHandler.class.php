<?php

namespace ch\timesplinter\core;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 */
class RequestHandler
{
	public static function redirect($uri)
	{
		$headers = array(
			'Location' => $uri,
			'X-Framework-Redirect' => 'true'
		);

		$httpResponse = new HttpResponse(301, null, $headers);
		$httpResponse->send();

		exit;
	}

	public static function redirectHttpRequestToSSL(HttpRequest $httpRequest)
	{
		if($httpRequest->getProtocol() === HttpRequest::PROTOCOL_HTTPS)
			return;

		$httpResponse = new HttpResponse(301, null, array(
			'Location' => 'https://' . $httpRequest->getHost() . $httpRequest->getURI()
		));
		
		$httpResponse->send();

		exit;
	}
}

/* EOF */