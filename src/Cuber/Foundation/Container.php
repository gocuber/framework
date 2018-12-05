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

    /**
     * Global container
     *
     * @var static
     */
    protected static $instance;

    /**
     * bindings
     *
     * @var array
     */
    protected $bindings;

    /**
     * instances
     *
     * @var array
     */
    protected $instances;

    /**
     * aliases
     *
     * @var array
     */
    protected $aliases;

    /**
     * Set container instance
     *
     * @return static Container
     */
    public static function setInstance(Container $container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Get container instance
     *
     * @return static Container
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Binding shared
     *
     * @param string $abstract
     * @param \Closure|string $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Binding if
     *
     * @param string $abstract
     * @param \Closure|string $concrete
     * @param bool $shared
     * @return void
     */
    public function bindIf($abstract, $concrete = null, $shared = false)
    {
        if (! $this->bound($abstract)) {
            $this->bind($abstract, $concrete, $shared);
        }
    }

    /**
     * Binding
     *
     * @param string $abstract
     * @param \Closure|string $concrete
     * @param bool $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        if ($concrete instanceof Closure) {
            $this->bindings[$abstract] = compact('concrete', 'shared');
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    /**
     * Make
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
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

    /**
     * Bound
     *
     * @param string $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]) or isset($this->instances[$abstract]) or $this->isAlias($abstract);
    }

    /**
     * Is alias
     *
     * @param string $name
     * @return bool
     */
    public function isAlias($name)
    {
        return isset($this->aliases[$name]);
    }

    /**
     * Alias
     *
     * @param string $abstract
     * @param string $alias
     * @return void
     */
    public function alias($abstract, $alias)
    {
        $abstract !== $alias and $this->aliases[$alias] = $abstract;
    }

    /**
     * Get alias
     *
     * @param string $abstract
     * @return string
     */
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

    /**
     * Offset exists
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->bound($key);
    }

    /**
     * Offset get
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->make($key);
    }

    /**
     * Offset set
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->bind($key, $value instanceof Closure ? $value : function () use ($value) {
            return $value;
        });
    }

    /**
     * Offset unset
     *
     * @param string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->bindings[$key], $this->instances[$key]);
    }

    /**
     * Get
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this[$key];
    }

    /**
     * Set
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }

    /**
     * Flush the container of all bindings and instances
     *
     * @return void
     */
    public function flush()
    {
        $this->aliases = [];
        $this->bindings = [];
        $this->instances = [];
    }

}
