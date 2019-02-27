<?php

namespace Cuber\Support\Facades;

use Cuber\Foundation\Container;

abstract class Facade
{

    public static function __callStatic($method, $args)
    {
        return Container::getInstance()->make(static::$accessor)->$method(...$args);
    }

}
