<?php

/**
 * Description of Cache
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Metanet AG
 * @version 1.0
 */
abstract class TurboCache {
	protected $cacheChanged;
	
	public function __construct() {
		$this->cacheChanged = false;
	}
	
	abstract public function init();
	abstract public function close();
	abstract public function getValue($key);
	abstract public function storeValue($key, $value, $gzip);
}

?>
