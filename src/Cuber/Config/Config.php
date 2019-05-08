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
