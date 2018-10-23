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

    protected $binds;

    protected $instances;

    private function __construct()
    {}

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * bind
     *
     * @param string $abstract
     * @param Closure|string|array|Object|Class $concrete
     *
     * @return void
     */
    public function bind($abstract, $concrete)
    {
        if ($concrete instanceof \Closure) {
            $this->binds[$abstract] = $concrete;
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    /**
     * make
     *
     * @param string $abstract
     * @param array $parameters
     *
     * @return mixed
     */
    public function make($abstract, $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        array_unshift($parameters, $this);

        return call_user_func_array($this->binds[$abstract], $parameters);
    }

    /**
     * get
     *
     * @param string $key
     * @param mixed $default
     *
     * @return array|string
     */
    public function get($key = null, $default = null)
    {
        if (isset($key)) {
            return \array_get($this->instances, $key, $default);
        } else {
            return $this->instances;
        }
    }

}
