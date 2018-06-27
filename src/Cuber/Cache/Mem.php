<?php

/**
 * Mem
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

class Mem
{

    private function __construct(){}

    public static function connect($conf = null)
    {
        empty($conf) and $conf = 'default';

        if(!is_array($conf)){
            $conf = (!empty($GLOBALS['_G']['memcache'][$conf]) and is_array($GLOBALS['_G']['memcache'][$conf])) ? $GLOBALS['_G']['memcache'][$conf] : array();
        }

        $mem = extension_loaded('Memcached') ? 'Cache_Memcached' : 'Cache_Memcache';
        return $mem::connect($conf);
    }

}
