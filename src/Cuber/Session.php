<?php

/**
 * Session
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Session
{

    private static $_is_start = null;

    /**
     * start
     *
     * @return boolean
     */
    public static function start()
    {
        if(!isset(self::$_is_start)){
            self::$_is_start = true;
            session_start();
        }
        return true;
    }

    /**
     * id
     *
     * @param string $id
     * @return string
     */
    public static function id($id = null)
    {
        if(isset($id)){
            return session_id($id);
        }else{
            return session_id();
        }
    }

    /**
     * get
     *
     * @param string $name
     * @return string|null
     */
    public static function get($name = null)
    {
        Session::start();
        $value = isset($_SESSION[$name]) ? $_SESSION[$name] : null;
        return $value;
    }

    /**
     * set
     *
     * @param string $name
     * @param string $value
     * @return boolean
     */
    public static function set($name = null, $value = null)
    {
        Session::start();
        $_SESSION[$name] = $value;
        return true;
    }

    /**
     * del
     *
     * @param string $name
     * @return boolean
     */
    public static function del($name = null)
    {
        Session::start();
        unset($_SESSION[$name]);
        return true;
    }

    /**
     * destroy
     *
     * @return boolean
     */
    public static function destroy()
    {
        Session::start();
        unset($_SESSION);
        session_unset();
        session_destroy();
        return true;
    }

}
