<?php

/**
 * Container
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class Container implements ArrayAccess
{

    protected static $instance;

    protected $hash;

    protected $bindings;

    protected $instances;

    private function __construct()
    {}

    public static function getInstance($class = 'Cuber\\Foundation\\Container')
    {
        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new $class();
        }

        return self::$instance[$class];
    }

    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    public function bind($abstract, $concrete = null, $shared = false)
    {
        if ($concrete instanceof \Closure) {
            $this->binds[$abstract] = ['concrete'=>$concrete, 'shared'=>$shared];
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    public function make($abstract, array $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!isset($this->binds[$abstract])) {
            return null;
        }

        $result = call_user_func_array($this->binds[$abstract]['concrete'], $parameters);
        // $result = $this->binds[$abstract]['concrete'](...$parameters);

        if ($this->binds[$abstract]['shared']) {
            $this->instances[$abstract] = (null === $result) ? false : $result;
        }

        return $result;
    }

    /**
     * set
     *
     * @param string|array $key
     * @param string|array $value
     *
     * @return bool
     */
    public function set($key = null, $value = null)
    {
        if (!isset($key) or '' === $key) {
            return false;
        }

        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->hash[$k] = $v;
            }
        } elseif (is_scalar($key)) {
            $this->hash[$key] = $value;
        }

        return true;
    }

    /**
     * get
     *
     * @param string $key
     * @param string|array $default
     *
     * @return string|array
     */
    public function get($key = null, $default = null)
    {
        if (null === $key) {
            return $this->hash;
        } else {
            return array_get($this->hash, $key, $default);
        }
    }

    /**
     * @param offset
     */
    public function offsetExists($offset) {}

    /**
     * @param offset
     */
    public function offsetGet($offset) {}

    /**
     * @param offset
     * @param value
     */
    public function offsetSet($offset, $value) {}

    /**
     * @param offset
     */
    public function offsetUnset($offset) {}

}
