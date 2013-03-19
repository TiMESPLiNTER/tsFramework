<?php

namespace ch\timesplinter\caching;

use \stdClass;

/**
 * Description of CacheInterface
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.0.0
 */
interface CacheInterface {
	public function init(stdClass $settings);
	public function close();
	public function get($key);
	public function put($key, $value, $expiration);
    public function delete($key);
}

/* EOF */