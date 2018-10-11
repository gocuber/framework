<?php

/**
 * Session
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

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
        if (!isset(self::$_is_start)) {
            session_start();
            self::$_is_start = true;
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
        if (isset($id)) {
            return session_id($id);
        } else {
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
        self::start();
        if (isset($name)) {
            return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
        } else {
            return $_SESSION;
        }
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
        self::start();
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
        self::start();
        if (isset($name)) {
            unset($_SESSION[$name]);
        } else {
            unset($_SESSION);
        }
        return true;
    }

    /**
     * destroy
     *
     * @return boolean
     */
    public static function destroy()
    {
        self::start();
        unset($_SESSION);
        session_unset();
        session_destroy();
        return true;
    }

}
