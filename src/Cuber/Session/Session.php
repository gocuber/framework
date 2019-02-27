<?php

/**
 * Session
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

use Cuber\Support\Facades\Cookie;
use Cuber\Cache\File;

class Session
{

    private $init;

    private $cache;

    private $session_data;

    private $session_id;

    private $is_save;

    private function init()
    {
        if (null === $this->init) {
            $this->id();

            $driver = config('session.driver');
            $key = config('session.connect');
            if ('memcache' == $driver) {
                $this->cache = Mem::connect($key);
            } elseif ('redis' == $driver) {
                $this->cache = Redis::connect($key);
            } else {
                $this->cache = File::connect($key);
            }

            $session = $this->cache->get(config('session.prefix', '') . $this->session_id);
            $this->session_data = $session ? unserialize($session) : [];

            $this->init = true;
        }
    }

    /**
     * set
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function set($key = null, $value = null)
    {
        if (!isset($key)) {
            return false;
        }

        $this->init();
        $this->session_data[$key] = $value;
        $this->is_save = true;
        return true;
    }

    /**
     * get
     *
     * @param string $key
     * @return string
     */
    public function get($key = null)
    {
        $this->init();
        if (isset($key)) {
            return isset($this->session_data[$key]) ? $this->session_data[$key] : null;
        } else {
            return $this->session_data;
        }
    }

    /**
     * del
     *
     * @param string $key
     * @return bool
     */
    public function del($key = null)
    {
        $this->init();
        if (isset($this->session_data[$key])) {
            unset($this->session_data[$key]);
        } else {
            unset($this->session_data);
        }

        $this->is_save = true;
        return true;
    }

    /**
     * 生成一个 session_id
     *
     * @return string
     */
    public function createId()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * 设置 session_id
     *
     * @param string $id
     * @return $this
     */
    public function id($id = null)
    {
        if (!empty($id)) {
            $this->session_id = $id;
            return $this;
        }

        $cookie = config('session.cookie', 'CUBERSESSID0OO00OOO0OO00O00O0O00OO00O');
        $id = Cookie::get($cookie);
        if (empty($id)) {
            $id = $this->createId();
            Cookie::set($cookie, $id, 86400 * 3600);
        }
        $this->session_id = $id;
        return $this;
    }

    /**
     * save
     *
     * @param string $id
     * @return bool
     */
    private function save()
    {
        if (null === $this->is_save) {
            return true;
        }

        $driver = config('session.driver');
        if ('memcache' == $driver) {
            $this->cache->set(config('session.prefix', '') . $this->session_id, serialize($this->session_data), config('session.time', 86400));
        } else {
            $this->cache->set(config('session.prefix', '') . $this->session_id, serialize($this->session_data));
        }

        $this->is_save = null;
        return true;
    }

    /**
     * __destruct
     *
     * @return void
     */
    public function __destruct()
    {
        $this->save();
    }

}
