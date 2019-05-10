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
     * 创建 cookie 驱动
     *
     * @param  Cookie  $cookie
     * @param  array   $config
     * @return void
     */
    public function __construct(Cookie $cookie, $config = [])
    {
        $this->cookie = $cookie;
        $this->expire = array_get($config, 'expire', 86400 * 7);
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
        return $this->cookie->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        if (0 == $this->expire) {
            return $this->cookie->forever($id, $data);
        } else {
            return $this->cookie->make($id, $data, $this->expire);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        return $this->cookie->forget($id);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}
