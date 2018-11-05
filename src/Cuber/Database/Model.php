<?php

/**
 * Model
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Database;

use Cuber\Support\Facades\DB;

abstract class Model
{

    public $connect = 'default';

    public $name = '';

    public $fields = [];

    public static function __callStatic($method, $args)
    {
        $st = static::class;

        return DB::model(new $st())->$method(...$args);
    }

    public function __call($method, $args)
    {
        return DB::model($this)->$method(...$args);
    }

}
