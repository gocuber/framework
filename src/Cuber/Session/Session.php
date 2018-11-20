<?php

/**
 * Session
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

class Session
{

    private $is_start = null;

    /**
     * start
     *
     * @return boolean
     */
    public function start()
    {
        if (null === $this->is_start) {
            session_start();
            $this->is_start = true;
        }

        return true;
    }

    /**
     * id
     *
     * @param string $id
     * @return string
     */
    public function id($id = null)
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
    public function get($name = null)
    {
        $this->start();
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
    public function set($name = null, $value = null)
    {
        $this->start();
        $_SESSION[$name] = $value;
        return true;
    }

    /**
     * del
     *
     * @param string $name
     * @return boolean
     */
    public function del($name = null)
    {
        $this->start();
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
    public function destroy()
    {
        $this->start();
        unset($_SESSION);
        session_unset();
        session_destroy();
        return true;
    }

}
