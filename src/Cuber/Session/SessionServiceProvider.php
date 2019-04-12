<?php

/**
 * SessionServiceProvider
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

class SessionServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('session.store', function ($app) {
            return $app->make('session')->driver();
        });

        app()->singleton('session', function ($app) {
            return new SessionManager($app);
        });
    }

}
