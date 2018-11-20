<?php

/**
 * Session
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

use Cuber\Cookie\Cookie;
use Cuber\Config\Config;

class Session
{

    private $init = null;

    private $session_data = null;

    private $session_id = null;

    private $is_save = null;

    private function __construct()
    {}

    private function init()
    {
        if (null === $this->init) {
            $this->id();

            $driver = Config::get('session.driver');
            $key    = Config::get('session.connect');
            if ('memcache' == $driver) {
                $this->_cache = Mem::connect($key);
            } elseif ('redis' == $driver) {
                $this->_cache = Redis::connect($key);
            } else {
                $this->_cache = File::connect($key);
            }

            $session = $this->_cache->get(Config::get('session.prefix', '') . $this->_session_id);
            $this->_session = $session ? unserialize($session) : [];
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
        if (!isset($key) or !isset($value)) {
            return false;
        }

        $this->init();
        $this->_session[$key] = $value;
        $this->_is_save = true;
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
            return isset($this->_session[$key]) ? $this->_session[$key] : null;
        } else {
            return $this->_session;
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
        if (isset($this->_session[$key])) {
            unset($this->_session[$key]);
        } else {
            unset($this->_session);
        }

        $this->_is_save = true;
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

        $cookie = Config::get('session.cookie', 'CUBERSESSID0OO00OOO0OO00O00O0O00OO00O');
        $id = Cookie::get($cookie);
        if (empty($id)) {
            $id = $this->createId();
            Cookie::set($cookie, $id, 86400);
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

        $driver = Config::get('session.driver');
        if ('memcache' == $driver) {
            $this->_cache->set(Config::get('session.prefix', '') . $this->session_id, serialize($this->_session), Config::get('session.time', 86400));
        } else {
            $this->_cache->set(Config::get('session.prefix', '') . $this->session_id, serialize($this->_session));
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
