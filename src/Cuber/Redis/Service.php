<?php

/**
 * Service
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Redis;

class Service
{

    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('redis', function () {
            return new Redis(config('redis', []));
        });

        app()->bind('redis.connect', function ($config) {
            return new Connect($config);
        });
    }

}
