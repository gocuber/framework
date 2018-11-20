<?php

/**
 * AliasLoader
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Config\Config;

class AliasLoader
{

    private $_alias = null;

    public function __construct()
    {
        $this->init();
    }

    /**
     * init
     *
     * @return void
     */
    private function init()
    {
        $this->_alias = Config::alias();
    }

    /**
     * Register
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register([$this, 'load'], true, true);
    }

    /**
     * Load
     *
     * @param string $alias
     *
     * @return bool|void
     */
    private function load($alias)
    {
        if (isset($this->_alias[$alias])) {
            return class_alias($this->_alias[$alias], $alias);
        }
    }

}
