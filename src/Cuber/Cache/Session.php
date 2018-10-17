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

    private static $_instance = null;

    private $_session_id = null;

    private $_session = null;

    private $_is_save = false;

    private $_cache = null;

    private function __construct($id = null)
    {
        $this->setSessionId($id);

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

    public static function getInstance($id = null)
    {
        $key = md5($id);
        if (!isset(self::$_instance[$key])) {
            self::$_instance[$key] = new self($id);
        }

        return self::$_instance[$key];
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
    public function id()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * setSessionId
     *
     * @param string $id
     * @return bool
     */
    private function setSessionId($id = null)
    {
        if (!empty($id)) {
            $this->_session_id = $id;
            return true;
        }

        $cookie = Config::get('session.cookie', 'CUBERSESSID0OO00OOO0OO00O00O0O00OO00O');
        $id = Cookie::get($cookie);
        if (empty($id)) {
            $id = self::id();
            Cookie::set($cookie, $id, 86400);
        }
        $this->_session_id = $id;
        return true;
    }

    /**
     * save
     *
     * @param string $id
     * @return bool
     */
    private function save()
    {
        if (false == $this->_is_save) {
            return true;
        }

        $driver = Config::get('session.driver');
        if ('memcache' == $driver) {
            $this->_cache->set(Config::get('session.prefix', '') . $this->_session_id, serialize($this->_session), Config::get('session.time', 86400));
        } else {
            $this->_cache->set(Config::get('session.prefix', '') . $this->_session_id, serialize($this->_session));
        }

        $this->_is_save = false;
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
