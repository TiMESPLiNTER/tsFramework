<?php

namespace ch\timesplinter\caching;

use ch\timesplinter\caching\CacheInterface;
use \stdClass;

/**
 * Description of FileCache
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.0.0
 */
class FileCache implements CacheInterface {
    private $changed;
    private $cacheFile;
    private $header;
    private $values;
    private $settings;

    public function __construct() {
        $this->changed = false;
        $this->values = new stdClass;
        $this->header = new stdClass;
    }

    public function init(stdClass $settings) {
        $this->settings = $settings;
        list($this->header, $this->values) = unserialize(($settings->gzip === true)?$this->getGZipFileContent($this->cacheFile):$this->cacheFile);
    }

    public function close() {
        if($this->changed === false)
            return true;

        if($this->settings->gzip === true) {
            if(($gfp = gzopen($this->cacheFile, 'w')) === false)
                return false;

            gzwrite($gfp, serialize(array($this->header, $this->values)));
            gzclose($gfp);
        } else {
            if(($fp = fopen($this->cacheFile, 'w')) === false)
                return false;

            fwrite($fp, serialize(array($this->header, $this->values)));
            fclose($fp);
        }

        return true;
    }

    public function get($key) {
        if(isset($this->values->$key) === false)
            return null;

        if(isset($this->header->$key->expires) === false || $this->header->$key->expires == 0 || $this->header->$key->expires >= time())
            return $this->values->$key;

        $this->delete($key);

        return null;
    }

    public function put($key, $value, $expiration = 0) {
        if(isset($this->values->$key) === true && $this->values->$key !== $value && $this->header->$key->expires === $expiration) {
            return true;
        }

        $this->changed = true;
        $this->values->$key = $value;

        if(isset($this->header->$key) === false)
            $this->header->$key = new stdClass;

        $this->header->$key->expires = ($expiration > 0)?time() + $expiration:0;

        return true;
    }

    public function delete($key) {
        unset($this->header->$key);
        unset($this->values->$key);

        $this->changed = true;

        return true;
    }

    private function getGZipFileContent($gzipFile) {
        if(file_exists($gzipFile) === false)
            return new stdClass;

        if(($gfp = gzopen($gzipFile, 'r')) === false)
            throw new CacheException('Could not read cache file: ' . $this->cacheFile);

        ob_start();

        gzpassthru($gfp);
        gzclose($gfp);

        return ob_get_clean();
    }
}

/* EOF */