<?php

/**
 * MemcacheServiceProvider
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Memcache;

class MemcacheServiceProvider
{

    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('memcache.memcache', function () {
            return new \Cuber\Memcache\Memcache(config('memcache', []));
        });

        app()->singleton('memcache.memcached', function () {
            return new \Cuber\Memcache\Memcached(config('memcache', []));
        });

        app()->bind('memcache', function () {
            return new \Cuber\Memcache\MemcacheManager(app('memcache.' . config('memcache.driver', 'memcached')), config('memcache', []));
        });
    }

}
