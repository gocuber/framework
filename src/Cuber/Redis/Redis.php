<?php

/**
 * Redis
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Redis;

class Redis
{

    private $configs;

    private $connects;

    public function __construct($configs = [])
    {
        $this->configs = $configs;
    }

    /**
     * connect
     *
     * @return Redis
     */
    public function connect($key = 'default', $mode = 'master')
    {
        $conf = array_get($this->configs, $key);

        $conn_key = $key . '.' . $mode;
        if (isset($this->connects[$conn_key])) {
            return $this->connects[$conn_key];
        }

        if ('slave' == $mode and !empty($conf['slave']) and is_array($conf['slave'])) {
            $skey = mt_rand(0, count($conf['slave']) - 1);
            $conf = array_merge($conf, $conf['slave'][$skey]);
        }

        $this->connects[$conn_key] = app('redis.connect', [$conf]);
        return $this->connects[$conn_key];
    }

    /**
     * master
     *
     * @return Redis
     */
    public function master($key = 'default')
    {
        return $this->connect($key, 'master');
    }

    /**
     * slave
     *
     * @return Redis
     */
    public static function slave($key = 'default')
    {
        return $this->connect($key, 'slave');
    }

    public function __call($method, $args)
    {
        return $this->connect()->$method(...$args);
    }

}
