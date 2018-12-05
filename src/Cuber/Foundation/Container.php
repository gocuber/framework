<?php

/**
 * Container
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Closure;
use ArrayAccess;

class Container implements ArrayAccess
{

    protected static $instance;

    protected $bindings;

    protected $instances;

    protected $aliases;

    public static function setInstance(Container $container = null)
    {
        return static::$instance = $container;
    }

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    public function bindIf($abstract, $concrete = null, $shared = false)
    {
        if (! $this->bound($abstract)) {
            $this->bind($abstract, $concrete, $shared);
        }
    }

    public function bind($abstract, $concrete = null, $shared = false)
    {
        if ($concrete instanceof Closure) {
            $this->bindings[$abstract] = compact('concrete', 'shared');
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

        if (! isset($this->bindings[$abstract])) {
            return null;
        }

        $result = call_user_func_array($this->bindings[$abstract]['concrete'], $parameters);

        if ($this->bindings[$abstract]['shared']) {
            $this->instances[$abstract] = (null === $result) ? false : $result;
        }

        return $result;
    }

    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]) or isset($this->instances[$abstract]) or $this->isAlias($abstract);
    }

    public function isAlias($name)
    {
        return isset($this->aliases[$name]);
    }

    public function alias($abstract, $alias)
    {
        $abstract !== $alias and $this->aliases[$alias] = $abstract;
    }

    public function getAlias($abstract)
    {
        if (! isset($this->aliases[$abstract])) {
            return $abstract;
        }

        if ($this->aliases[$abstract] === $abstract) {
            return $abstract;
        }

        return $this->getAlias($this->aliases[$abstract]);
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

    public function __get($key)
    {
        return $this[$key];
    }

    public function __set($key, $value)
    {
        $this[$key] = $value;
    }

}
