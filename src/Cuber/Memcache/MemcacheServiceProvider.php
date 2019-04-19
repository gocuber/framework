<?php

/**
 * ServiceProvider
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
        app()->singleton('memcache', function () {
            return new \Cuber\Memcache\Memcache(config('memcache', []));
        });

        app()->singleton('memcached', function () {
            return new \Cuber\Memcache\Memcached(config('memcache', []));
        });

        app()->bind('MemcacheManager', function () {
            return new \Cuber\Memcache\MemcacheManager(app(config('memcache.driver', 'memcached')));
        });
    }

}
