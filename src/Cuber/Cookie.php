<?php

/**
 * Cookie
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Cookie
{

    private static $_prefix = ''; //COOKIE_

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
        return self::$_prefix . $key;
    }

}
