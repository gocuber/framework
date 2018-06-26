<?php

/**
 * Cache_Mem
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Cache_Mem
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
