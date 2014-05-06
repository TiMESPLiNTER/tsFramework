<?php

namespace ch\timesplinter\core;

/**
 * The basic class where plugins for the tsFramework have to be based on
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER Webdevelopment
 */
abstract class FrameworkPlugin {
	/** @var Core $core */
	protected $core;

	/**
	 * @param Core $core A framework core instance
	 */
	public function __construct(Core $core) {
		$this->core = $core;
	}

	/**
	 * Invoked before the HttpRequest object gets built
	 */
	public function beforeRequestBuilt() {
		
	}

	/**
	 * Invoked after the framework has built the HttpRequest object
	 */
	public function afterRequestBuilt() {
		
	}

	/**
	 * Invoked before the request URI gets matched against the available routes and the controller method gets called
	 */
	public function beforeResponseBuilt() {
		
	}

	/**
	 * Invoked after the request URI tried to match against a route and the linked controller method was called
	 */
	public function afterResponseBuilt() {
		
	}

	/**
	 * Invoked after response has been generated. So you can manipulate the final response by accessing it with
	 * the call of the specific getter method of the Core instance
	 */
	public function beforeResponseSent() {
		
	}

	/**
	 * Invoked after the response has been sent to the client
	 */
	public function afterResponseSent() {
		
	}
}

/* EOF */