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
     * @param string $type
     * @param string $key
     * @param string|array $default
     *
     * @return array
     */
    private static function get($type = 'db', $key = null, $default = [])
    {
    	self::set();

    	if (!isset(self::$hash[$type])) {
    		return $default;
    	}

    	if (isset($key)) {
            return isset(self::$hash[$type][$key]) ? self::$hash[$type][$key] : $default;
    	} else {
    		return self::$hash[$type];
    	}
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
    	return self::get('db', $key);
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
    	return self::get('memcache', $key);
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
    	return self::get('redis', $key);
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
    	return self::get('filecache', $key);
    }

    /**
     * Get Alias Config
     *
     * @return array
     */
    public static function alias()
    {
    	return self::get('alias', null, []);
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

    	return self::get($key, null, '');
    }

    /**
     * Get moduleDomain Config
     *
     * @param string $key
     *
     * @return str
     */
    public static function moduleDomain($key = '')
    {
    	return self::get('module_domain', $key, '');
    }

}
