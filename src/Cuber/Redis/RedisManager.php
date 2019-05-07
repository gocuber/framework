<?php

/**
 * RedisManager
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Redis;

class RedisManager
{

    private $driver;

    private $connect = 'default';

    private $mode = 'master';

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function connect($key = 'default', $mode = 'master')
    {
        $this->connect = $key;
        $this->mode = $mode;

        return $this;
    }

    public function master($key = 'default')
    {
        return $this->connect($key, 'master');
    }

    public function slave($key = 'default')
    {
        return $this->connect($key, 'slave');
    }

    public function driver()
    {
        // connect() 避免driver由于单例模式而默认连接到上次使用的connect

        return $this->driver->connect($this->connect, $this->mode);
    }

    public function __call($name, $args)
    {
        return $this->driver()->$name(...$args);
    }

}
