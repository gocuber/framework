<?php

/**
 * AliasLoader
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class AliasLoader
{

    private $alias;

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
        $this->alias = config('alias');
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
        if (isset($this->alias[$alias])) {
            return class_alias($this->alias[$alias], $alias);
        }
    }

}
