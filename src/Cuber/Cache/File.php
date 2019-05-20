<?php

/**
 * File
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

use Cuber\FileCache\FileCache;

class File implements Store
{

    private $file;

    public function __construct(FileCache $file)
    {
        $this->file = $file;
    }

    public function config($config)
    {
        return $this;
    }

    public function connect($key)
    {
        $this->file->connect($key);

        return $this;
    }

    public function set($key = null, $value = null, $expire = 0)
    {
        return $this->file->set($key, $value, $expire);
    }

    public function get($key = null)
    {
        return $this->file->get($key);
    }

    public function delete($key = null)
    {
        return $this->file->delete($key);
    }

    public function mSet(array $keys = [], $expire = 0)
    {
        return $this->file->mSet($keys, $expire);
    }

    public function mGet(array $keys = [])
    {
        return $this->file->mGet($keys);
    }

    public function mDelete(array $keys = [])
    {
        return $this->file->mDelete($keys);
    }

    public function increment($key = null, $value = 1)
    {
        return 0;
    }

    public function decrement($key = null, $value = 1)
    {
        return 0;
    }

}
