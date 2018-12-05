<?php

/**
 * AliasLoader
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class AliasLoader
{

    private $aliases;

    public function __construct(array $aliases = [])
    {
        $this->aliases = $aliases;
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
        if (isset($this->aliases[$alias])) {
            return class_alias($this->aliases[$alias], $alias);
        }
    }

}
