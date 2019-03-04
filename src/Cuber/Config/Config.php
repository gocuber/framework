<?php

/**
 * Config
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Config;

class Config
{

    private $hash;

    /**
     * init
     *
     * @return void
     */
    private function init()
    {
        if (null === $this->hash) {
            $this->hash = include base_path('config/app.php');
        }
    }

    /**
     * set
     *
     * @param string|array $key
     * @param string|array $value
     *
     * @return bool
     */
    public function set($key = null, $value = null)
    {
        if (!isset($key) or '' === $key) {
            return false;
        }

        $this->init();

        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->hash[$k] = $v;
            }
        } elseif (is_scalar($key)) {
            $this->hash[$key] = $value;
        }

        return true;
    }

    /**
     * Get Config
     *
     * @param string $key
     * @param type   $default
     *
     * @return conf
     */
    public function get($key = null, $default = null)
    {
        $this->init();

        return array_get($this->hash, $key, $default);
    }

    /**
     * Get Database Config
     *
     * @param string $key
     *
     * @return array
     */
    public function db($key = 'default')
    {
        $key = isset($key) ? 'db.' . $key : 'db';

        return $this->get($key, []);
    }

    /**
     * Get Memcache Config
     *
     * @param string $key
     *
     * @return array
     */
    public function mem($key = 'default')
    {
        $key = isset($key) ? 'memcache.' . $key : 'memcache';

        return $this->get($key, []);
    }

    /**
     * Get Redis Config
     *
     * @param string $key
     *
     * @return array
     */
    public function redis($key = 'default')
    {
        $key = isset($key) ? 'redis.' . $key : 'redis';

        return $this->get($key, []);
    }

    /**
     * Get Filecache Config
     *
     * @param string $key
     *
     * @return array
     */
    public function fc($key = 'default')
    {
        $key = isset($key) ? 'filecache.' . $key : 'filecache';

        return $this->get($key, []);
    }

    /**
     * Get Alias Config
     *
     * @return array
     */
    public function alias()
    {
        return $this->get('alias', []);
    }

    /**
     * Get timezone Config
     *
     * @return str
     */
    public function timezone()
    {
        return $this->get('timezone', 'PRC');
    }

    /**
     * Get debug Config
     *
     * @return bool
     */
    public function debug()
    {
        return $this->get('debug', false);
    }

    /**
     * Get charset Config
     *
     * @return str
     */
    public function charset()
    {
        return $this->get('charset', 'utf-8');
    }

}
