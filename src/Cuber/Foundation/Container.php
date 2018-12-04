<?php

/**
 * Container
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class Container
{

    protected static $instance;

    protected $bindings;

    protected $instances;

    protected $aliases;

    private function __construct()
    {}

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    public function bind($abstract, $concrete = null, $shared = false)
    {
        if ($concrete instanceof \Closure) {
            $this->bindings[$abstract] = ['concrete'=>$concrete, 'shared'=>$shared];
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    public function make($abstract, array $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!isset($this->bindings[$abstract])) {
            return null;
        }

        $result = call_user_func_array($this->bindings[$abstract]['concrete'], $parameters);
        // $result = $this->bindings[$abstract]['concrete'](...$parameters);

        if ($this->bindings[$abstract]['shared']) {
            $this->instances[$abstract] = (null === $result) ? false : $result;
        }

        return $result;
    }

    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]) or isset($this->instances[$abstract]);
    }

}
