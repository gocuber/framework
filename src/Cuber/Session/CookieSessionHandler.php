<?php

/**
 * CookieSessionHandler
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

use SessionHandlerInterface;
use Cuber\Cookie\Cookie;

class CookieSessionHandler implements SessionHandlerInterface
{

    /**
     * Cookie
     *
     * @var Cuber\Cookie\Cookie
     */
    private $cookie;

    /**
     * 过期秒数
     *
     * @var int
     */
    private $expire;

    /**
     * Cookie前缀
     *
     * @var string
     */
    private $prefix = 'CUBERSESS_';

    /**
     * 创建 cookie 驱动
     *
     * @param  Cookie  $cookie
     * @param  int  $expire
     * @return void
     */
    public function __construct(Cookie $cookie, $expire = 86400 * 7)
    {
        $this->cookie = $cookie;
        $this->expire = $expire;
    }

    /**
     * {@inheritdoc}
     */
    public function open($save_path, $session_name)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($id)
    {
        return json_decode($this->cookie->get($this->prefix . $id), true);
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        return $this->cookie->make($this->prefix . $id, json_encode($data), $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        return $this->cookie->forget($this->prefix . $id);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}
