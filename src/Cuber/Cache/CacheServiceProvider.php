<?php

/**
 * CacheServiceProvider
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

class CacheServiceProvider
{

    /**
     * 注册服务
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('cache.file', function () {
            return new \Cuber\Cache\File(app('filecache'));
        });

        app()->singleton('cache.mysql', function () {
            return new \Cuber\Cache\Mysql(app('db'));
        });

        app()->singleton('cache.memcache', function () {
            return new \Cuber\Cache\Memcache(app('memcache'));
        });

        app()->singleton('cache.redis', function () {
            return new \Cuber\Cache\Redis(app('redis'));
        });

        app()->bind('cache', function () {
            return new \Cuber\Cache\CacheManager(app(), config('cache', []));
        });
    }

}
