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

    protected $connect = 'default';

    protected $name = '';

    protected $fields = [];

    public static function __callStatic($method, $args)
    {
        $st = static::class;

        return DB::model(new $st())->$method(...$args);
    }

    public function __call($method, $args)
    {
        return DB::model($this)->$method(...$args);
    }

    public function getConnect()
    {
        return $this->connect;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFields()
    {
        return $this->fields;
    }

}
