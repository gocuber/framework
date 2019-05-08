<?php

/**
 * Config
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Config;

class Config
{

    private $hash;

    /**
     * init
     *
     * @return void
     */
    private function init()
    {
        if (null === $this->hash) {
            $this->hash = include base_path('config/app.php');
        }
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

        $this->init();

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
     * Get Config
     *
     * @param string $key
     * @param type   $default
     *
     * @return conf
     */
    public function get($key = null, $default = null)
    {
        $this->init();

        return array_get($this->hash, $key, $default);
    }

}
