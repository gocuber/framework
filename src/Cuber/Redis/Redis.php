<?php

/**
 * Redis
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Redis;

class Redis
{

    private static $instance;

    /**
     * connect
     *
     * @return Redis
     */
    public static function connect($key = 'default', $mode = 'master')
    {
        $conf = config('redis.' . $key);
        $key = md5($conf['host'] . '_' . $conf['port'] . '_' . $conf['database'] . '_' . $mode);

        if (!isset(self::$instance[$key])) {
            if ('slave' == $mode and !empty($conf['slave']) and is_array($conf['slave'])) {
                $skey = mt_rand(0, count($conf['slave']) - 1);
                $conf = array_merge($conf, $conf['slave'][$skey]);
            }
            self::$instance[$key] = new Connect($conf);
        }

        return self::$instance[$key];
    }

    /**
     * master
     *
     * @return Redis
     */
    public static function master($key = 'default')
    {
        return self::connect($key, 'master');
    }

    /**
     * slave
     *
     * @return Redis
     */
    public static function slave($key = 'default')
    {
        return self::connect($key, 'slave');
    }

    public static function __callStatic($method, $args)
    {
        return self::connect()->$method(...$args);
    }

}
