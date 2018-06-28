<?php

/**
 * Mem
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

class Mem
{

    public static function connect($key = 'default')
    {
        $mem = extension_loaded('Memcached') ? 'Cuber\\Cache\\Memcached' : 'Cuber\\Cache\\Memcache';
        return $mem::connect($key);
    }

}
