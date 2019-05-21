<?php

/**
 * helpers
 *
 * @author Cuber <dafei.net@gmail.com>
 */

/**
 * htmlspecialchars
 *
 * @return string
 */
function e()
{
    return htmlspecialchars(...func_get_args());
}

/**
 * header 404
 *
 * @return void
 */
function header404()
{
    header('HTTP/1.1 404 Not Found');
}

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

/**
 * argvs
 *
 * @return array
 */
function argvs()
{
    $_argv = $GLOBALS['argv'];
    $_argc = $GLOBALS['argc'];

    $argvs = [];
    for ($i = 2; $i < $_argc; $i++) {
        $key = $_argv[$i];
        if ('-' != substr($key, 0, 1)) {
            continue 1;
        }
        $i++;
        $value = $_argv[$i];
        if ('-' == substr($value, 0, 1)) {
            $i--;
            continue 1;
        }
        $argvs[substr($key, 1)] = $value;
    }

    return $argvs;
}

/**
 * app container instance
 *
 * @param  string  $abstract
 * @param  array   $parameters
 * @return mixed|\Cuber\Foundation\Container
 */
function app($abstract = null, array $parameters = [])
{
    if (is_null($abstract)) {
        return Cuber\Foundation\Container::getInstance();
    }

    return Cuber\Foundation\Container::getInstance()->make($abstract, $parameters);
}

/**
 * view
 *
 * @see Cuber\Foundation\View
 */
function view()
{
    if (func_num_args() > 0) {
        return app('view')->display(...func_get_args());
    } else {
        return app('view');
    }
}

/**
 * request
 *
 * @param  mixed|null  $key
 * @param  mixed|null  $default
 *
 * @return mixed|\Cuber\Support\Request
 */
function request($key = null, $default = null)
{
    if (is_null($key)) {
        return app('request');
    }

    return app('request')->param($key, $default);
}

/**
 * cookie
 *
 * @param  string   $name
 * @param  string   $value
 * @param  int      $expire
 * @param  string   $path
 * @param  string   $domain
 * @param  bool     $secure
 * @param  bool     $httponly
 *
 * @return mixed|\Cuber\Cookie\Cookie
 */
function cookie($name = null, $value = null, $expire = 0, $path = null, $domain = null, $secure = null, $httponly = null)
{
    if (is_null($name)) {
        return app('cookie');
    }

    return app('cookie')->make($name, $value, $expire, $path, $domain, $secure, $httponly);
}

/**
 * session
 *
 * @param  mixed|null  $key
 * @param  mixed|null  $default
 *
 * @return mixed|\Cuber\Session\SessionManager
 */
function session($key = null, $default = null)
{
    if (is_null($key)) {
        return app('session');
    }

    return app('session')->get($key, $default);
}

/**
 * cache
 *
 * @param  mixed|null  $key
 * @param  mixed|null  $default
 *
 * @return mixed|\Cuber\Cache\CacheManager
 */
function cache($key = null, $default = null)
{
    if (null === $key) {
        return app('cache');
    }

    if (is_array($key)) {
        return app('cache')->set(key($key), reset($key), $default);
    }

    return app('cache')->get($key, $default);
}

/**
 * config
 *
 * @param  mixed|null  $key
 * @param  mixed|null  $default
 *
 * @return mixed|\Cuber\Config\Config
 */
function config($key = null, $default = null)
{
    if (null === $key) {
        return app('config');
    }

    if (is_array($key)) {
        return app('config')->set($key);
    }

    return app('config')->get($key, $default);
}

/**
 * base_path
 *
 * @return string
 */
function base_path($path = '')
{
    return app('app.base_path') . $path;
}

/**
 * url
 *
 * @param string $url
 * @param string $domain
 * @param string $http
 *
 * @return string
 */
function url($url = '', $domain = '', $http = '//')
{
    if (isset($domain) and '' !== $domain) {
        return $http . $domain . $url;
    } else {
        return $url;
    }
}

/**
 * array_get
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $default
 *
 * @return value
 */
function array_get($array = [], $key = null, $default = null)
{
    if (empty($array) or !is_array($array)) {
        return $default;
    }

    if (null === $key) {
        return $array;
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

/**
 * put_env
 *
 * @return void
 */
function put_env()
{
    $config = base_path('.env');
    if (is_file($config)) {
        $conf = parse_ini_file($config, false);
        if (!empty($conf) and is_array($conf)) {
            foreach ($conf as $key=>$value) {
                putenv("{$key}={$value}");
            }
        }
    }
}

/**
 * getenv
 *
 * @param  string  $key
 * @param  mixed   $default
 *
 * @return mixed
 */
function env($key, $default = null)
{
    $value = getenv($key);

    return ($value === false) ? $default : $value;
}

/**
 * model
 *
 * @return new Model
 */
function model($model)
{
    $model = config('model_namespace', 'App\\Models\\') . $model;

    return new $model;
}

/**
 * 创建可写目录
 *
 * @param string $dir
 *
 * @return bool
 */
function mk_dir($dir)
{
    if (empty($dir)) {
        return false;
    }

    if (!is_writable($dir)) {
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true)) {
                return false;
            }
        } else {
            return @chmod($dir, 0777);
        }
    }

    return true;
}

/**
 * 文件日志
 *
 * @param  string  $file
 * @param  string  $data
 * @param  string  $mode
 *
 * @return bool
 */
function file_log($file, $data, $mode = 'ab')
{
    if (empty($file)) {
        return false;
    }

    if (!mk_dir(dirname($file))) {
        return false;
    }

    $handle = fopen($file, $mode);
    fwrite($handle, $data);
    fclose($handle);
    return true;
}
