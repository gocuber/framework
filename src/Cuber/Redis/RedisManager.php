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

    private $config;

    private $connect;

    private $mode = 'master';

    public function __construct($driver, $config)
    {
        $this->driver = $driver;
        $this->config = $config;
        $this->connect();
    }

    public function connect($key = 'default')
    {
        if (null === $key) {
            $this->connect = array_get($this->config, 'default', 'default');
        } else {
            $this->connect = $key;
        }

        return $this;
    }

    public function master()
    {
        $this->mode = 'master';

        return $this;
    }

    public function slave()
    {
        $this->mode = 'slave';

        return $this;
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
