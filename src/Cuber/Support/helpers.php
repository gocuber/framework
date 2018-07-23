<?php

/**
 * helpers
 *
 * @author Cuber <dafei.net@gmail.com>
 */

if (! function_exists('is_cli')) {
    /**
     * 是否CLI运行
     *
     * @return bool
     */
    function is_cli()
    {
        if (defined('IS_CLI')) {
            return IS_CLI;
        }
        return ('cli' === php_sapi_name());
    }
}

if (! function_exists('ret404')) {
    /**
     * header 404
     *
     * @return void
     */
    function ret404()
    {
        header('HTTP/1.1 404 Not Found');
    }
}

if (! function_exists('get_argv')) {
    /**
     * get_argv
     *
     * @return array
     */
    function get_argv()
    {
        $_argv = $GLOBALS['argv'];
        $_argc = $GLOBALS['argc'];
        $argvs = [];
        for($i = 2; $i < $_argc; $i++){
            $key = $_argv[$i];
            if('-' != substr($key, 0, 1)){
                continue 1;
            }
            $i++;
            $value = $_argv[$i];
            if('-' == substr($value, 0, 1)){
                $i--;
                continue 1;
            }
            $argvs[$key] = $value;
        }
        return $argvs;
    }
}

if (! function_exists('s')) {
    /**
     * s()
     *
     * @param string|array $data
     * @param bool $exit
     *
     * @return void
     */
    function s($data = null, $exit = false)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        $exit and exit();
    }
}

if (! function_exists('d')) {
    /**
     * d()
     *
     * @return void
     */
    function d()
    {
        echo '<pre>';
        debug_print_backtrace();
        echo '</pre>';
    }
}

if (! function_exists('array_get')) {
    /**
     * array_get
     *
     * @return value
     */
    function array_get($array = [], $key = null, $default = null)
    {
        if (empty($array) or !is_array($array)) {
            return $default;
        }

        if (!isset($key)) {
            return $default;
        }

        $key = explode('.', $key);

        foreach ($key as $k) {
            if (isset($array[$k])) {
                $array = $array[$k];
            } else {
                return $default;
            }
        }

        return $array;
    }
}

if (! function_exists('env')) {
    /**
     * getenv
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        return ($value === false) ? $default : $value;
    }
}

if (! function_exists('model')) {
    /**
     * model
     *
     * @return new Model()
     */
    function model($model = null)
    {
        return Cuber\Database\DB::model($model);
    }
}

if (! function_exists('mk_dir')) {
    /**
     * 创建目录
     *
     * @param string $dir
     *
     * @return bool
     */
    function mk_dir($dir = null)
    {
        if (empty($dir)) {
            return false;
        }

        if (!is_writable($dir)) {
            if (!@mkdir($dir, 0777, true)) {
                return false;
            }
        }

        //@chmod($dir, 0777);
        return true;
    }
}
