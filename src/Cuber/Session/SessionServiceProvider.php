<?php

/**
 * SessionServiceProvider
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

class SessionServiceProvider
{

    /**
     * 注册 Session 服务
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('session.file', function () {
            return new \Cuber\Session\FileSessionHandler();
        });
        app()->singleton('session.cookie', function () {
            return new \Cuber\Session\CookieSessionHandler();
        });
        app()->singleton('session.mysql', function () {
            return new \Cuber\Session\MysqlSessionHandler();
        });
        app()->singleton('session.memcache', function () {
            return new \Cuber\Session\MemcacheSessionHandler();
        });
        app()->singleton('session.redis', function () {
            return new \Cuber\Session\RedisSessionHandler();
        });
        app()->singleton('session', function ($id = null) {
            return new \Cuber\Session\SessionManager('session.' . config('session.driver', 'file'), $id);
        });
    }

}
