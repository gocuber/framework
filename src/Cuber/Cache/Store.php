<?php

/**
 * Store
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

interface Store
{

    /**
     * set
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $expire
     *
     * @return bool
     */
    public function set($key, $value, $expire);

    /**
     * get
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * delete
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function delete($key);

    /**
     * mSet
     *
     * @param  array  $keys
     * @param  int    $expire
     *
     * @return bool
     */
    public function mSet(array $keys, $expire);

    /**
     * mGet
     *
     * @param  array  $keys
     *
     * @return array|null
     */
    public function mGet(array $keys);

    /**
     * mDelete
     *
     * @param  array  $keys
     *
     * @return bool
     */
    public function mDelete(array $keys);

    /**
     * increment
     *
     * @param  string  $key
     * @param  int     $value
     *
     * @return bool
     */
    public function increment($key, $value = 1);

    /**
     * decrement
     *
     * @param  string  $key
     * @param  int     $value
     *
     * @return bool
     */
    public function decrement($key, $value = 1);

}
