<?php

/**
 * DatabaseServiceProvider
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Database;

class DatabaseServiceProvider
{

    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        app()->bind('db', function () {
            return new \Cuber\Database\DB();
        });
    }

}
