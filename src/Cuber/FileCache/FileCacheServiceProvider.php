<?php

/**
 * FileCacheServiceProvider
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\FileCache;

class FileCacheServiceProvider
{

    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        app()->bind('filecache', function () {
            return new \Cuber\FileCache\FileCache(config('filecache', []));
        });
    }

}
