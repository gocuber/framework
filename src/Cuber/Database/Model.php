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
        $m = new $st();

        return DB::model($m)->connect($m->getConnect())->name($m->getName())->$method(...$args);
    }

    public function __call($method, $args)
    {
        return DB::model($this)->connect($this->connect)->name($this->name)->$method(...$args);
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
