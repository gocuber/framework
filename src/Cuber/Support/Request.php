<?php

/**
 * Request
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Support;

class Request
{

    /**
     * get
     *
     * @param string $key
     * @param string $default
     *
     * @return string|array
     */
    public function get($key = null, $default = null)
    {
        return $this->arrayGet($_GET, $key, $default);
    }

    /**
     * post
     *
     * @param string $key
     * @param string $default
     *
     * @return string|array
     */
    public function post($key = null, $default = null)
    {
        return $this->arrayGet($_POST, $key, $default);
    }

    /**
     * request
     *
     * @param string $key
     * @param string $default
     *
     * @return string|array
     */
    public function request($key = null, $default = null)
    {
        return $this->arrayGet($_REQUEST, $key, $default);
    }

    /**
     * argv
     *
     * @param string $key
     * @param string $default
     *
     * @return string|array
     */
    public function argv($key = null, $default = null)
    {
        return $this->arrayGet($this->getArgv(), $key, $default);
    }

    /**
     * param
     *
     * @param string $key
     * @param string $default
     *
     * @return string|array
     */
    public function param($key = null, $default = null)
    {
        return $this->arrayGet($this->getParam(), $key, $default);
    }

    /**
     * cookie
     *
     * @param string $key
     * @param string $default
     *
     * @return string|array
     */
    public function cookie($key = null, $default = null)
    {
        return $this->arrayGet($_COOKIE, $key, $default);
    }

    /**
     * arrayGet
     *
     * @param array $data
     * @param string $key
     * @param string $default
     *
     * @return string|array
     */
    private function arrayGet($data = [], $key = null, $default = null)
    {
        if (null === $key) {
            return $data;
        }

        return array_get($data, $key, $default);
    }

    /**
     * getArgv
     *
     * @return array
     */
    private function getArgv()
    {
        if (!is_cli()) {
            return [];
        }

        if (null === app('app.argv')) {
            app()->bind('app.argv', argvs());
        }

        return app('app.argv');
    }

    /**
     * getParam
     *
     * @return array
     */
    private function getParam()
    {
        if (null === app('app.param')) {
            app()->bind('app.param', array_merge($_REQUEST, $this->getArgv()));
        }

        return app('app.param');
    }

}
