<?php
namespace ch\timesplinter\caching;

/**
 * Description of Memcached
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Metanet AG
 * @version 1.0
 */

use \Memcached;
use \stdClass;

class MemcachedCache extends Memcached implements CacheInterface {
    public function init(stdClass $settings)  {
        $connection = false;

        foreach($settings->pool as $poolEntry) {
            if($this->addServer($poolEntry->host, $poolEntry->port, $poolEntry->weight) !== false)
                $connection = true;
        }

        if($connection === false)
            throw new CacheException('Could not access cache');
    }

    public function close() {
        if($this->quit() === false)
            throw new CacheException('Could not close cache');
    }

    public function get($key) {
        return $this->get($key);
    }

    public function put($key, $value, $expiration) {
        $this->put($key, $value, $expiration);
    }

    public function delete($key) {
        $this->delete($key);
    }
}

/* EOF */