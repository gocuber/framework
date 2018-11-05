<?php

namespace Cuber\Support\Facades;

use Cuber\Foundation\Container;

abstract class Facade
{

    protected static $type = 'instance';

    public static function __callStatic($method, $args)
    {
        if ('object' == static::$type) {
            return (new static::$accessor())->$method(...$args);
        } else {
            return Container::getInstance(static::$accessor)->$method(...$args);
        }
    }

}
