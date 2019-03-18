<?php

/**
 * Request
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Support;

class Request
{

    public function get($key = null, $default = null)
    {
        return $this->_get($_GET, $key, $default);
    }

    public function post($key = null, $default = null)
    {
        return $this->_get($_POST, $key, $default);
    }

    public function request($key = null, $default = null)
    {
        return $this->_get($_REQUEST, $key, $default);
    }

    public function argv($key = null, $default = null)
    {
        return $this->_get(app('app.argv'), $key, $default);
    }

    public function param($key = null, $default = null)
    {
        if (null === app('app.param')) {
            $argv = app('app.argv') ? app('app.argv') : [];
            app('app.param', array_merge($_REQUEST, $argv));
        }

        return $this->_get(app('app.param'), $key, $default);
    }

    /**
     * _get
     *
     * @param array $data  $_GET|$_POST|$_REQUEST|app('app.argv')|app('app.param')
     * @param string $key
     * @param string $default
     *
     * @return string|array
     */
    private function _get($data = [], $key = null, $default = null)
    {
        if (null === $key or '' === $key) {
            return $data;
        } else {
            return array_get($data, $key, $default);
        }
    }

}
