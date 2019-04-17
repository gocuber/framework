<?php

/**
 * ServiceProvider
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cookie;

class CookieServiceProvider
{

    /**
     * Register
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('cookie', function () {
            return new \Cuber\Cookie\Cookie();
        });
    }

}
