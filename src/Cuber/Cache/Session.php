<?php

/**
 * Session
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

class Session
{

    private static $_instance = null;

    private $_cookie_key = 'CUBESESSID0OO00OOO00O00O00OO00O';

    private $_session_id = null;

    private $_session = null;

    private $_is_save = false;

    private $_cache_prefix = 'CACHESESSION_';

    public static $_cache_type = 'mem';

    private $_cache = null;

    private function __construct($id = null)
    {
    	$this->setSessionId($id);

    	if('file' == self::$_cache_type){
    	    $this->_cache = Cache_File::connect('session');
    	}else{
    	    $this->_cache = Cache_Mem::connect();
    	}

    	$this->_session = $this->_cache->get($this->_cache_prefix . $this->_session_id);
    }

    public static function getInstance($id = null)
    {
        $key = md5($id);
        if(!isset(self::$_instance[$key])){
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
        if(!isset($key) or !isset($value)){
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
        if(isset($key)){
            return isset($this->_session[$key]) ? $this->_session[$key] : null;
        }else{
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
        if(isset($this->_session[$key])){
        	unset($this->_session[$key]);
        }else{
        	unset($this->_session);
        }

        $this->_is_save = true;
        return true;
    }

    /**
     * getSessionId
     *
     * @return string
     */
    public function getSessionId()
    {
    	return $this->_session_id;
    }

    /**
     * setSessionId
     *
     * @param string $id
     * @return bool
     */
    private function setSessionId($id = null)
    {
        if(!empty($id)){
            $this->_session_id = $id;
            return true;
        }

        $id = Cookie::get($this->_cookie_key);
        if(empty($id)){
            $id = md5(uniqid(mt_rand(), true));
            Cookie::set($this->_cookie_key, $id, 86400);
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
        if(false == $this->_is_save){
            return true;
        }

        $this->_cache->set($this->_cache_prefix . $this->_session_id, $this->_session, 86400);
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
