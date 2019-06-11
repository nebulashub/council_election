<?php
namespace Phalcon\Cache\Backend;

class RedisCluster extends \RedisCluster {

    public function settimeout($seconds) {

    }

    public function delete($key) {
        return $this->del($key);
    }

}