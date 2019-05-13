<?php

/**
 * RedisServiceProvider
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Redis;

class RedisServiceProvider
{

    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('redis.redis', function () {
            return new \Cuber\Redis\Redis(config('redis', []));
        });

        app()->bind('redis', function () {
            return new \Cuber\Redis\RedisManager(app('redis.' . config('redis.driver', 'redis')));
        });
    }

}
