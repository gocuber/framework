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
        app()->singleton('db.mysql', function () {
            return new \Cuber\Database\Mysql();
        });

        app()->bind('db.query', function () {
            return new \Cuber\Database\Query();
        });

        app()->bind('db', function () {
            return new \Cuber\Database\DatabaseManager(app(), config('db', []));
        });
    }

}
