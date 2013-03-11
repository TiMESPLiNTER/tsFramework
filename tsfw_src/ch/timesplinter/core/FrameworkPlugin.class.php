<?php

namespace ch\timesplinter\core;

use ch\timesplinter\core\Core;

/**
 * Description of Plugin
 *
 * @author pascal91
 */
abstract class FrameworkPlugin {
	/** @var Core $core */
	protected $core;
	
	public function __construct(Core $core) {
		$this->core = $core;
	}
	
	public function beforeRequestBuilt() {
		
	}
	
	public function afterRequestBuilt() {
		
	}
	
	public function beforeResponseBuilt() {
		
	}
	
	public function afterResponseBuilt() {
		
	}
	
	public function beforeResponseSent() {
		
	}
	
	public function afterResponseSent() {
		
	}
}

/* EOF */