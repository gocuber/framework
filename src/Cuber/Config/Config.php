<?php

/**
 * Config
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Config;

class Config
{

    private static $hash = null;

    private function __construct(){}

    /**
     * init
     *
     * @return void
     */
    private static function set()
    {
        if (!isset(self::$hash)) {
            self::$hash = include BASE_PATH . 'config/app.php';
        }
    }

    /**
     * Get Config
     *
     * @param string $key
     * @param type   $default
     *
     * @return conf
     */
    public static function get($key = null, $default = null)
    {
        self::set();

        return array_get(self::$hash, $key, $default);
    }

    /**
     * Get Database Config
     *
     * @param string $key
     *
     * @return array
     */
    public static function db($key = 'default')
    {
        $key = isset($key) ? 'db.' . $key : 'db';

        return self::get($key, []);
    }

    /**
     * Get Memcache Config
     *
     * @param string $key
     *
     * @return array
     */
    public static function mem($key = 'default')
    {
        $key = isset($key) ? 'memcache.' . $key : 'memcache';

        return self::get($key, []);
    }

    /**
     * Get Redis Config
     *
     * @param string $key
     *
     * @return array
     */
    public static function redis($key = 'default')
    {
        $key = isset($key) ? 'redis.' . $key : 'redis';

        return self::get($key, []);
    }

    /**
     * Get Filecache Config
     *
     * @param string $key
     *
     * @return array
     */
    public static function fc($key = 'default')
    {
        $key = isset($key) ? 'filecache.' . $key : 'filecache';

        return self::get($key, []);
    }

    /**
     * Get Alias Config
     *
     * @return array
     */
    public static function alias()
    {
        return self::get('alias', []);
    }

    /**
     * Get Domain Config
     *
     * @param string $key
     *
     * @return str
     */
    public static function domain($key = null)
    {
        $key = isset($key) ? $key . '_domain' : 'domain';

        return self::get($key);
    }

    /**
     * Get moduleDomain Config
     *
     * @param string $key
     *
     * @return str
     */
    public static function moduleDomain($key = null)
    {
        if (!isset($key)) {
            return self::get('domain');
        }

        return self::get('module.' . $key . '.domain');
    }

    /**
     * Get timezone Config
     *
     * @return str
     */
    public static function timezone()
    {
        return self::get('timezone', 'PRC');
    }

    /**
     * Get debug Config
     *
     * @return bool
     */
    public static function debug()
    {
        return self::get('debug', false);
    }

    /**
     * Get charset Config
     *
     * @return str
     */
    public static function charset()
    {
        return self::get('charset', 'utf-8');
    }

}
