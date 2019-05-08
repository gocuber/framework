<?php

/**
 * MemcacheManager
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Memcache;

class MemcacheManager
{

    private $driver;

    private $connect = 'default';

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function connect($key = 'default')
    {
        $this->connect = $key;

        return $this;
    }

    public function driver()
    {
        // connect() 避免driver由于单例模式而默认连接到上次使用的connect

        return $this->driver->connect($this->connect);
    }

    public function __call($name, $args)
    {
        return $this->driver()->$name(...$args);
    }

}
