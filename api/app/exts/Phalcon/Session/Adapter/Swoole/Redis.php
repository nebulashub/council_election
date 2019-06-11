<?php
namespace Phalcon\Session\Adapter\Swoole;

use Phalcon\Cache\Backend\RedisExt as RedisBackend;
use Phalcon\Cache\Frontend\None as FrontendNone;
use Phalcon\Session\Adapter\Redis as RedisAapter;

class Redis extends RedisAapter
{
    const SESSION_ID_NAME = "SESSION_ID";

    private $_id = null;

    public function reconnect()
    {
        $this->_redis = new RedisBackend(
            new FrontendNone(["lifetime" => $this->_lifetime]),
            $this->_options
        );
        return $this;
    }

    protected function _getFromRedis($key, $lifetime)
    {
        try {
            $data = $this->_redis->get($key, $lifetime);
        } catch (\Exception $e) {
            $this->reconnect();
            $data = $this->_getFromRedis($key, $lifetime);
        }
        return $data;
    }

    public function __construct(array $options = [])
    {
        $lifetime = null;

        if (!isset($options["host"])) {
            $options["host"] = "127.0.0.1";
        }

        if (!isset($options["port"])) {
            $options["port"] = 6379;
        }

        if (!isset($options["persistent"])) {
            $options["persistent"] = false;
        }
        if (isset($options["lifetime"])) {
            $this->_lifetime = (int)$options["lifetime"];
        }
        $this->_redis = new RedisBackend(
            new FrontendNone(["lifetime" => $this->_lifetime]),
            $options
        );

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function recoverId($id)
    {
        $ret = $this->_getFromRedis($id, $this->_lifetime);
        if (empty($ret)) {
            return false;
        }
        $this->setId($id);
        return true;
    }

    public function _read()
    {
        return unserialize($this->_getFromRedis($this->getId(), $this->_lifetime));
    }

    public function _write($sessionData, ?int $lifetime = null)
    {
        $sessionData = serialize($sessionData);
        if ($this->_redis instanceof \RedisCluster) {
            $lifetime = 0;
        }
        try {
            $this->_redis->save($this->getId(), $sessionData, $lifetime === null ? $this->_lifetime : $lifetime);
        } catch (\Phalcon\Cache\Exception $e) {
            $this->reconnect();
            $this->_redis->save($this->getId(), $sessionData, $lifetime === null ? $this->_lifetime : $lifetime);
        }
    }

    public function get($index, $defaultValue = null, $remove = null)
    {
        $sessionData = $this->_read();

        $uniqueId = $this->_uniqueId;
        if (!empty($uniqueId)) {
            $index = $uniqueId . "#" . $index;
        } else {
            $index = $index;
        }
        if ($value = isset($sessionData[$index]) ? $sessionData[$index] : null) {
            if ($remove) {
                $this->remove($index);
            }
            return $value;
        }
        return $defaultValue;
    }

    public function set($index, $value)
    {
        $args = func_get_args();
        $lifetime = count($args) > 2 ? $args[2] : null;

        $sessionData = $this->_read();
        $uniqueId = $this->_uniqueId;
        if (!empty($uniqueId)) {
            $index = $uniqueId . "#" . $index;
        } else {
            $index = $index;
        }
        $sessionData[$index] = $value;
        $this->_write($sessionData, $lifetime);
    }

    public function remove($index)
    {
        $sessionData = $this->_read();

        $uniqueId = $this->_uniqueId;
        if (!empty($uniqueId)) {
            $index = $uniqueId . "#" . $index;
        } else {
            $index = $index;
        }
        if (isset($sessionData[$index])) {
            unset($sessionData[$index]);
            $this->_write($sessionData);
        }
    }

    public function destroy($sessionId = null)
    {
        if ($sessionId === null) {
            $id = $this->getId();
        } else {
            $id = $sessionId;
        }
        return $this->_redis->exists($id) ? $this->_redis->delete($id) : true;
    }

}
