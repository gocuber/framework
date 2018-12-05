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
     * register
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('route', function () {
            return new \Cuber\Foundation\Route();
        });
        app()->singleton('view', function () {
            return new \Cuber\Foundation\View();
        });
        app()->singleton('cookie', function () {
            return new \Cuber\Cookie\Cookie();
        });
        app()->singleton('session', function () {
            return new \Cuber\Session\Session();
        });
        app()->singleton('request', function () {
            return new \Cuber\Support\Requese();
        });
    }

}
