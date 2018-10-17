<?php

/**
 * Cookie
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cookie;

use Cuber\Config\Config;

class Cookie
{

    /**
     * get
     *
     * @param string $name
     * @return string|null
     */
    public static function get($name = null)
    {
        $name = self::buildKey($name);
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * set
     *
     * @param string $name
     * @param string $value
     * @param int $time
     * @param string $path
     * @param string $domain
     * @return bool
     */
    public static function set($name = null, $value = null, $time = 3600, $path = '/', $domain = null)
    {
        $name = self::buildKey($name);
        !isset($domain) and $domain = Config::get('cookie.domain');
        setcookie($name, $value, time() + $time, $path, $domain);
        $_COOKIE[$name] = $value;
        return true;
    }

    /**
     * setraw
     *
     * @param string $name
     * @param string $value
     * @param int $time
     * @param string $path
     * @param string $domain
     * @return bool
     */
    public static function setraw($name = null, $value = null, $time = 3600, $path = '/', $domain = null)
    {
        $name = self::buildKey($name);
        !isset($domain) and $domain = Config::get('cookie.domain');
        setrawcookie($name, $value, time() + $time, $path, $domain);
        return true;
    }

    /**
     * del
     *
     * @param string $name
     * @return bool
     */
    public static function del($name = null)
    {
        $name = self::buildKey($name);
        setcookie($name, null, time() - 3600);
        unset($_COOKIE[$name]);
        return true;
    }

    /**
     * buildKey
     *
     * @param string $key
     * @return string
     */
    private static function buildKey($key = null)
    {
        return Config::get('cookie.prefix', '') . $key;
    }

}
