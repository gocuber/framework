<?php

/**
 * Memcache
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

use Cuber\Memcache\MemcacheManager;

class Memcache implements Store
{

    private $cache;

    public function __construct(MemcacheManager $cache)
    {
        $this->cache = $cache;
    }

    public function config($config)
    {
        return $this;
    }

    public function connect($key)
    {
        $this->cache->connect($key);

        return $this;
    }

    public function set($key = null, $value = null, $expire = 0)
    {
        return $this->cache->set($key, $value, $expire);
    }

    public function get($key = null)
    {
        return $this->cache->get($key);
    }

    public function delete($key = null)
    {
        return $this->cache->delete($key);
    }

    public function setMulti(array $keys = [], $expire = 0)
    {
        return $this->cache->setMulti($keys, $expire);
    }

    public function getMulti(array $keys = [])
    {
        return $this->cache->getMulti($keys);
    }

    public function deleteMulti(array $keys = [])
    {
        return $this->cache->deleteMulti($keys);
    }

    public function increment($key = null, $value = 1)
    {
        return $this->cache->increment($key, $value);
    }

    public function decrement($key = null, $value = 1)
    {
        return $this->cache->decrement($key, $value);
    }

}
