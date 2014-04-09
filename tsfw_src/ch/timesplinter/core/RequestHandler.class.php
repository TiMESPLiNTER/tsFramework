<?php

namespace ch\timesplinter\core;

/**
 * Description of RequestHandler
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 */
class RequestHandler {
    public static function redirect($uri) {
		$headers = array(
			 'Location' => $uri
		);
		
		$httpResponse = new HttpResponse(301, null, $headers);
		$httpResponse->send();
		
		exit;
	}

	public static function redirectHttpRequestToSSL(HttpRequest $httpRequest) {
		if($httpRequest->getProtocol() === HttpRequest::PROTOCOL_HTTPS)
			return;

		$headers = array(
			'Location' => 'https://' . $httpRequest->getHost() . $httpRequest->getURI()
		);

		$httpResponse = new HttpResponse(301, null, $headers);
		$httpResponse->send();

		exit;
	}
}

/* EOF */