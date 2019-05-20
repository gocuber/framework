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

    public function mSet(array $keys = [], $expire = 0)
    {
        return $this->cache->mSet($keys, $expire);
    }

    public function mGet(array $keys = [])
    {
        return $this->cache->mGet($keys);
    }

    public function mDelete(array $keys = [])
    {
        return $this->cache->mDelete($keys);
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
