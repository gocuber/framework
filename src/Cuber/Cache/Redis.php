<?php

/**
 * Redis
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

use Cuber\Redis\RedisManager;

class Redis implements Store
{

    private $redis;

    public function __construct(RedisManager $redis)
    {
        $this->redis = $redis;
    }

    public function config($config)
    {
        return $this;
    }

    public function connect($key)
    {
        $this->redis->connect($key);

        return $this;
    }

    public function set($key = null, $value = null, $expire = 0)
    {
        if (0 == $expire) {
            return $this->redis->set($key, $value);
        } else {
            return $this->redis->set($key, $value, $expire);
        }
    }

    public function get($key = null)
    {
        return $this->redis->get($key);
    }

    public function delete($key = null)
    {
        return $this->redis->delete($key);
    }

    public function setMulti(array $keys = [], $expire = 0)
    {
        if (0 == $expire) {
            return $this->redis->mSet($keys);
        } else {
            return $this->redis->mSetNx($keys, $expire);
        }
    }

    public function getMulti(array $keys = [])
    {
        return $this->redis->mGet($keys);
    }

    public function deleteMulti(array $keys = [])
    {
        return $this->redis->delete($keys);
    }

    public function increment($key = null, $value = 1)
    {
        return $this->redis->incrBy($key, $value);
    }

    public function decrement($key = null, $value = 1)
    {
        return $this->redis->decrBy($key, $value);
    }

}
