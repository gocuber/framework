<?php

/**
 * 核心服务提供者
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class CoreServiceProvider
{

    /**
     * Register
     *
     * @return void
     */
    public function register()
    {
        app()->bind('aliasloader', function ($aliases) {
            return new \Cuber\Foundation\AliasLoader($aliases);
        });

        app()->singleton('route', function () {
            return new \Cuber\Foundation\Route();
        });

        app()->singleton('view', function () {
            return new \Cuber\Foundation\View();
        });

        app()->singleton('request', function () {
            return new \Cuber\Support\Request();
        });

        app()->bind('db', function () {
            return new \Cuber\Database\DB();
        });
    }

}
