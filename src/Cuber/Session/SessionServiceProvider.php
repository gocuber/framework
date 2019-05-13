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
        app()->singleton('session.file', function ($config) {
            return new \Cuber\Session\FileSessionHandler(app('filecache'), $config);
        });

        app()->singleton('session.cookie', function ($config) {
            return new \Cuber\Session\CookieSessionHandler(app('cookie'), $config);
        });

        app()->singleton('session.mysql', function ($config) {
            return new \Cuber\Session\MysqlSessionHandler(app('db'), $config);
        });

        app()->singleton('session.memcache', function ($config) {
            return new \Cuber\Session\MemcacheSessionHandler(app('memcache'), $config);
        });

        app()->singleton('session.redis', function ($config) {
            return new \Cuber\Session\RedisSessionHandler(app('redis'), $config);
        });

        app()->singleton('session', function () {
            $config = config('session', []);
            $driver = app('session.' . config('session.driver', 'file'), [$config]);
            return new \Cuber\Session\SessionManager($driver, $config);
        });
    }

}
