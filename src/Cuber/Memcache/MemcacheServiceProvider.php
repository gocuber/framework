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
            return new \Cuber\Cache\Memcache(config('memcache', []));
        });

        app()->singleton('memcached', function () {
            return new \Cuber\Cache\Memcached(config('memcache', []));
        });

        app()->bind('MemcacheManager', function () {
            return new \Cuber\Cache\MemcacheManager(app(config('memcache.driver', 'memcache')));
        });
    }

}
