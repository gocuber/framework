<?php

/**
 * Container
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class Container implements \ArrayAccess
{

    protected static $instance;

    protected $bindings;

    protected $instances;

    protected $aliases;

    private function __construct()
    {}

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public static function setInstance(Container $container = null)
    {
        return static::$instance = $container;
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
        $abstract = $this->getAlias($abstract);

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

    public function getAlias($abstract)
    {
        if (! isset($this->aliases[$abstract])) {
            return $abstract;
        }

        if ($this->aliases[$abstract] === $abstract) {
            throw new \Exception("[{$abstract}] is aliased to itself.");
        }

        return $this->getAlias($this->aliases[$abstract]);
    }

    public function isAlias($name)
    {
        return isset($this->aliases[$name]);
    }

    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]) ||
               isset($this->instances[$abstract]) ||
               $this->isAlias($abstract);
    }

    public function offsetExists($key)
    {
        return $this->bound($key);
    }

    public function offsetGet($key)
    {
        return $this->make($key);
    }

    public function offsetSet($key, $value)
    {
        $this->bind($key, $value instanceof Closure ? $value : function () use ($value) {
            return $value;
        });
    }

    public function offsetUnset($key)
    {
        unset($this->bindings[$key], $this->instances[$key]);
    }

}
