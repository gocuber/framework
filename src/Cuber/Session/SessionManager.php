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

    /**
     * session 驱动
     *
     * @var SessionHandlerInterface
     */
    private $driver;

    /**
     * 存储中的 session_id 前缀
     *
     * @var string
     */
    private $prefix;

    /**
     * session id
     *
     * @var string
     */
    private $session_id;

    /**
     * session 数据
     *
     * @var array
     */
    private $session_data;

    /**
     * session数据是否改变 用于判断session数据是否需要写入存储
     *
     * @var bool
     */
    private $is_change = false;

    /**
     * Cookie中用来存储 Session ID 的 cookie key
     *
     * @var string
     */
    private $cookie_key;

    /**
     * __construct
     *
     * @param  SessionHandlerInterface $driver
     * @param  array  $config
     * @return void
     */
    public function __construct(SessionHandlerInterface $driver, $config = [])
    {
        $this->driver = $driver;
        $this->prefix = array_get($config, 'prefix', '');
        $this->cookie_key = array_get($config, 'cookie_key', 'CUBERSESSID');
        $this->id();
    }

    /**
     * 手动重新生成 Session ID 用于阻止 session fixation 攻击
     *
     * @return $this
     */
    public function regenerate()
    {
        $id = $this->createId();
        Cookie::forever($this->cookie_key, $id);

        $this->session_id = $id;
        // 下面两行 为了防止错误使用 产生错误的Session存储数据
        $this->session_data = [];
        $this->is_change = false;
        return $this;
    }

    /**
     * 切换 Session ID
     *
     * @param string $id
     * @return $this
     */
    public function id($id = null)
    {
        if (null === $id) {
            $id = Cookie::get($this->cookie_key);
            if (empty($id)) {
                $id = $this->createId();
                Cookie::forever($this->cookie_key, $id);
            }
        }

        $this->session_id = $id;
        $this->session_data = null;
        $this->is_change = false;
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
            $session = $this->driver->read($this->prefix . $this->session_id);
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
     * @return $this
     */
    public function set($name = null, $value = null)
    {
        if (!isset($name) or '' === $name) {
            return $this;
        }

        $this->initSessionData();
        $this->session_data[$name] = $value;
        $this->is_change = true;
        return $this;
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
     * @return $this
     */
    public function delete($name = null)
    {
        $this->initSessionData();

        if (null === $name) {
            $this->session_data = null;
        } else {
            if (isset($this->session_data[$name])) {
                unset($this->session_data[$name]);
            }
        }

        $this->is_change = true;
        return $this;
    }

    /**
     * 写入存储
     *
     * @return bool
     */
    public function write()
    {
        if (false === $this->is_change) {
            return true;
        }

        if (empty($this->session_data)) {
            $this->driver->destroy($this->prefix . $this->session_id);
        } else {
            $this->driver->write($this->prefix . $this->session_id, serialize($this->session_data));
        }
        $this->is_change = false;
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
