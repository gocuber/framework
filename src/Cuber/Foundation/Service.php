<?php

/**
 * Service
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class Service
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

        app()->singleton('cookie', function () {
            return new \Cuber\Cookie\Cookie();
        });

        app()->bind('db', function () {
            return new \Cuber\Database\DB();
        });
    }

}
