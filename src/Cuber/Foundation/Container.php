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

    protected $hash;

    private function __construct()
    {}

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * set
     *
     * @param string|array $key
     * @param string|array $value
     *
     * @return void
     */
    public function set($key = null, $value = null)
    {
        if (null !== $key and '' !== $key) {
            if (is_array($key)) {
                foreach ($key as $k=>$v) {
                    $this->hash[$k] = $v;
                }
            } elseif (is_scalar($key)) {
                $this->hash[$key] = $value;
            }
        }
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
        if (isset($key)) {
            return array_get($this->hash, $key, $default);
        } else {
            return $this->hash;
        }
    }

}
