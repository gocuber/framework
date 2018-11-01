<?php

/**
 * Model
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Database;

use Cuber\Foundation\Container;
use Cuber\Support\Facades\DB;

abstract class Model
{

    protected $connect = 'default';

    protected $name = '';

    protected $fields = [];

    private $query = null;

    /**
     * getQuery
     *
     * @return Query
     */
    protected function getQuery()
    {
        if (!isset($this->query)) {
            $this->query = DB::connect($this->connect)->name($this->name)->setFields($this->fields);
        }

        return $this->query;
    }

    public static function __callStatic($method, $args)
    {
        $st = static::class;
        return Container::getInstance($st)->getQuery()->$method(...$args);
    }

    public function __call($method, $args)
    {
        $st = static::class;
        s($st);
        s($method);
        s($args);
        return Container::getInstance($st)->getQuery()->$method(...$args);
    }

}
