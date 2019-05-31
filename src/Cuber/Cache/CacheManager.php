<?php

/**
 * CacheManager
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

class CacheManager
{

    /**
     * App
     *
     * @var App
     */
    private $app;

    /**
     * config
     *
     * @var array
     */
    private $config;

    /**
     * 驱动
     *
     * @var Cuber\Cache\Store
     */
    private $driver;

    /**
     * 前缀
     *
     * @var string
     */
    private $prefix;

    /**
     * __construct
     *
     * @param  App    $app
     * @param  array  $config
     *
     * @return void
     */
    public function __construct($app, $config = [])
    {
        $this->app = $app;
        $this->config = $config;
        $this->prefix = array_get($config, 'prefix', '');
        $this->store();
    }

    /**
     * store
     *
     * @param  string  $store
     *
     * @return $this
     */
    public function store($store = null)
    {
        if (null === $store) {
            $store = array_get($this->config, 'default', 'file');
        }

        $config = array_get($this->config, 'stores.' . $store, []);
        $driver = array_get($config, 'driver', 'file');

        $this->driver = $this->app->make('cache.' . $driver)
            ->config($config)
            ->connect(array_get($config, 'connect', 'cache'));

        return $this;
    }

    /**
     * set
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $expire
     *
     * @return bool
     */
    public function set($key = null, $value = null, $expire = 0)
    {
        return $this->driver->set($this->key($key), serialize($value), $expire);
    }

    /**
     * get
     *
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        $data = $this->driver->get($this->key($key), $default);

        if (null === $data) {
            return $default;
        } else {
            return unserialize($data);
        }
    }

    /**
     * delete
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function delete($key = null)
    {
        return $this->driver->delete($this->key($key));
    }

    /**
     * mSet
     *
     * @param  array  $keys
     * @param  int    $expire
     *
     * @return bool
     */
    public function mSet($keys = [], $expire = 0)
    {
        if (!is_array($keys)) {
            return false;
        }

        $data = [];
        foreach ($keys as $key=>$value) {
            $data[$this->key($key)] = serialize($value);
        }

        return $this->driver->mSet($data, $expire);
    }

    /**
     * mGet
     *
     * @param  array  $keys
     *
     * @return array|null
     */
    public function mGet($keys = [])
    {
        if (!is_array($keys)) {
            return null;
        }

        $hash_keys = [];
        foreach ($keys as $key) {
            $hash_keys[$this->key($key)] = $key;
        }
        $data = $this->driver->mGet(array_keys($hash_keys));

        if (null === $data) {
            return null;
        } else {
            $cacahe = [];
            foreach ($data as $key=>$value) {
                $cacahe[$hash_keys[$key]] = unserialize($value);
            }
            return $cacahe;
        }
    }

    /**
     * mDelete
     *
     * @param  array  $keys
     *
     * @return bool
     */
    public function mDelete($keys = [])
    {
        if (!is_array($keys)) {
            return false;
        }

        return $this->driver->mDelete(array_map([$this, 'key'], $keys));
    }

    /**
     * increment
     *
     * @param  array  $key
     * @param  int    $value
     *
     * @return array
     */
    public function increment($key = null, $value = 1)
    {
        return $this->driver->increment($this->key($key), (int)$value);
    }

    /**
     * decrement
     *
     * @param  array  $key
     * @param  int    $value
     *
     * @return array
     */
    public function decrement($key = null, $value = 1)
    {
        return $this->driver->decrement($this->key($key), (int)$value);
    }

    private function key($key = '')
    {
        return $this->prefix . md5($key);
    }

}
