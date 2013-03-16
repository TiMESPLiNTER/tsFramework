<?php

namespace ch\timesplinter\core;

/**
 * Description of RequestHandler
 *
 * @author Pascal Münst
 */
class RequestHandler {
	private $logger;
	
    public function __construct() {
		$this->logger = LoggerFactory::getLoggerByName('dev', $this);
    }
	
    public static function redirect($uri) {
		$headers = array(
			 'Location' => $uri
		);
		
		$httpResponse = new HttpResponse(301, null, $headers);
		$httpResponse->send();
		
		exit;
	}
}

/* EOF */