<?php

/**
 * SessionManager
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

use SessionHandlerInterface;
use Cuber\Support\Facades\Cookie;

class SessionManager
{

    private $driver;

    private $session_id;

    private $session_data;

    private $is_change;

    public function __construct(SessionHandlerInterface $driver, $id = null)
    {
        $this->driver = $driver;

        $this->id($id);
    }

    /**
     * 手动重新生成 session_id
     *
     * @return $this
     */
    public function regenerate()
    {
        $cookie = config('session.cookie', 'CUBERSESSID0OO00OOO0OO00O00O0O00OO00O');
        $id = Cookie::get($cookie);
        Cookie::forever($cookie, $id);

        $this->session_id = $id;
        $this->session_data = [];
        $this->is_change = null;
        return $this;
    }

    /**
     * id
     *
     * @param string $id
     * @return $this
     */
    public function id($id = null)
    {
        if (empty($id)) {
            $cookie = config('session.cookie', 'CUBERSESSID0OO00OOO0OO00O00O0O00OO00O');
            $id = Cookie::get($cookie);
            if (empty($id)) {
                $id = $this->createId();
                Cookie::forever($cookie, $id);
            }
        }

        $this->session_id = $id;
        $this->session_data = null;
        $this->is_change = null;
        return $this;
    }

    /**
     * 初始化获取session数据
     *
     * @return $this
     */
    private function initSessionData()
    {
        if (null === $this->session_data) {
            $session = $this->driver->read($this->session_id);
            $this->session_data = $session ? unserialize($session) : [];
        }

        return $this;
    }

    /**
     * 创建 session_id
     *
     * @return string
     */
    public function createId()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * 预设置
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function set($name = null, $value = null)
    {
        if (!isset($name) or '' === $name) {
            return false;
        }

        $this->initSessionData();
        $this->session_data[$name] = $value;
        $this->is_change = true;
        return true;
    }

    /**
     * 获取
     *
     * @param  string  $name
     * @param  string  $default
     *
     * @return string|array|null
     */
    public function get($name = null, $default = null)
    {
        $this->initSessionData();
        if (null === $name) {
            return $this->session_data;
        }

        return array_get($this->session_data, $name, $default);
    }

    /**
     * 删除
     *
     * @param string $name
     * @return bool
     */
    public function delete($name = null)
    {
        if (null === $name) {
            return false;
        }

        $this->initSessionData();
        if (isset($this->session_data[$name])) {
            unset($this->session_data[$name]);
            $this->is_change = true;
        }

        return true;
    }

    /**
     * 删除全部
     *
     * @return bool
     */
    public function destroy()
    {
        $this->driver->destroy($this->session_id);
        $this->session_data = null;
        $this->is_change = null;

        return true;
    }

    /**
     * 写入存储
     *
     * @return bool
     */
    public function write()
    {
        if (null === $this->is_change) {
            return true;
        }

        $this->driver->write($this->session_id, serialize($this->session_data));
        $this->is_change = null;
        return true;
    }

    /**
     * __destruct
     *
     * @return void
     */
    public function __destruct()
    {
        $this->write();
    }

}
