<?php
namespace Phalcon\Cache\Backend;


class RedisExt extends Redis {

    public function _connect()
    {
        $options = $this->_options;
        if ((!isset($options["host"]) || !isset($options["port"]) || !isset($options["persistent"])) && !isset($options["hosts"]) || !isset($options["timeout"])) {
            throw new Exception("Unexpected inconsistency in options");
        }
        $host       = $options["host"] ?? null;
        $port       = $options["port"] ?? null;
        $persistent = $options["persistent"] ?? null;
        $hosts      = $options["hosts"] ?? null;
        $timeout    = $options["timeout"];

        if (!empty($hosts)) {
            if ($hosts instanceof \Phalcon\Config) {
                $hosts = $hosts->toArray();
            }
            $redis = new RedisCluster(NULL, $hosts, $timeout, $timeout);
        } else {
            $redis = new \Redis();
            if ($persistent) {
                $success = $redis->pconnect($host, $port, $timeout);
            } else {
                $success = $redis->connect($host, $port, $timeout);
            }
            if (!$success) {
                throw new Exception("Could not connect to the Redisd server ".$host.":".$port);
            }
            if (isset($options["index"]) && ($index = $options["index"]) > 0) {
                $success = $redis->select($index);
                if (!$success) {
                    throw new Exception("Redis server selected database failed");
                }
            }
        }
        if (!empty($auth = $options["auth"])) {
            $success = $redis->auth($auth);
            if (!$success) {
                throw new Exception("Failed to authenticate with the Redisd server");
            }
        }
        $this->_redis = $redis;
    }

    public function getInstance()
    {
        if (!is_object($this->_redis)) {
            $this->_connect();
        }
        return $this->_redis;
    }
}
