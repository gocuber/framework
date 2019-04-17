<?php

/**
 * Cookie
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cookie;

class Cookie
{

    private $path = '/';

    private $domain = null;

    private $secure = false;

    private $httponly = false;

    /**
     * 设置默认 Cookie 配置
     *
     * @return $this
     */
    public function config($path = '/', $domain = null, $secure = false, $httponly = false)
    {
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httponly = $httponly;

        return $this;
    }

    /**
     * 创建 Cookie
     *
     * @param  string   $name
     * @param  string   $value
     * @param  int      $expire
     * @param  string   $path
     * @param  string   $domain
     * @param  bool     $secure
     * @param  bool     $httponly
     *
     * @return bool
     */
    public function make($name = null, $value = null, $expire = 0, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        return setcookie(
            $name,
            $value,
            $_SERVER['REQUEST_TIME'] + $expire,
            $path ? $path : $this->path,
            $domain ? $domain : $this->domain,
            is_bool($secure) ? $secure : $this->secure,
            is_bool($httponly) ? $httponly : $this->httponly
        );
    }

    /**
     * 创建永久 Cookie
     *
     * @param  string   $name
     * @param  string   $value
     * @param  string   $path
     * @param  string   $domain
     * @param  bool     $secure
     * @param  bool     $httponly
     *
     * @return bool
     */
    public function forever($name, $value, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        return $this->make($name, $value, 86400 * 3650, $path, $domain, $secure, $httponly);
    }

    /**
     * 使 Cookie 过期
     *
     * @param  string  $name
     * @param  string  $path
     * @param  string  $domain
     *
     * @return bool
     */
    public function forget($name, $path = null, $domain = null)
    {
        return $this->make($name, null, -3600, $path, $domain);
    }

    /**
     * 获取 Cookie
     *
     * @param  string  $name
     * @param  string  $default
     *
     * @return string|array|null
     */
    public function get($name = null, $default = null)
    {
        if (null === $name) {
            return $_COOKIE;
        }

        return array_get($_COOKIE, $name, $default);
    }

}
