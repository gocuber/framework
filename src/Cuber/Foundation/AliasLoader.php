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

    private static $_instance = null;

    private $_alias = null;

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Init
     *
     * @return $this
     */
    public function init()
    {
        $this->_alias = Config::alias();

        return $this;
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
