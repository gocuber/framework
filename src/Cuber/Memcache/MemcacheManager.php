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

    private $connect;

    private function __construct($driver)
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
        return $this->driver->connect($this->connect);
    }

    public function __call($name, $args)
    {
        return $this->driver()->$name(...$args);
    }

}
