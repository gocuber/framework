<?php

/**
 * helpers
 *
 * @author Cuber <dafei.net@gmail.com>
 */

/**
 * print_r
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

/**
 * var_dump
 *
 * @param string|array $data
 * @param bool $exit
 *
 * @return void
 */
function d($data = null, $exit = false)
{
    var_dump($data);
    $exit and exit();
}

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
function ret404()
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
 * get_argv
 *
 * @return array
 */
function get_argv()
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
    return app('view')->display(...func_get_args());
}

/**
 * request
 *
 * @param null|string $key
 * @param mixed $default
 * @param string $type  get|post|request|argv
 *
 * @return mixed
 */
function request($key = null, $default = null, $type = 'request')
{
    if (is_null($key)) {
        return app('request');
    }

    return app('request')->$type($key, $default);
}

/**
 * config
 *
 * @param string|array $key
 * @param string|array $default
 *
 * @return string|array
 */
function config($key = null, $default = null)
{
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
 * @return string
 */
function url($url = null)
{
    if (null === $url) {
        return app('url');
    } else {
        return app('url')->getUrl($url);
    }
}

/**
 * module_url
 *
 * @param string $module
 * @param string $url
 * @param string $http
 *
 * @return string
 */
function module_url($module = '', $url = '', $http = '//')
{
    return app('url')->getModuleUrl($module, $url, $http);
}

/**
 * domain_url
 *
 * @param string $domain
 * @param string $url
 * @param string $http
 *
 * @return string
 */
function domain_url($domain = '', $url = '', $http = '//')
{
    return app('url')->getDomainUrl($domain, $url, $http);
}

/**
 * res_url
 *
 * @param string $url
 * @param string $domain
 * @param string $http
 *
 * @return string
 */
function res_url($url = '', $domain = '', $http = '//')
{
    return app('url')->getResUrl($url, $domain, $http);
}

/**
 * array_get
 *
 * @param array $array
 * @param string $key
 * @param mixed $default
 *
 * @return value
 */
function array_get($array = [], $key = null, $default = null)
{
    if (empty($array) or !is_array($array)) {
        return $default;
    }

    if (null === $key or '' === $key) {
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

/**
 * put_env
 *
 * @return void
 */
function put_env()
{
    $config = base_path() . 'config/config.ini';
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

/**
 * model
 *
 * @return new Model()
 */
function model($model = null)
{
    $model = config('model_namespace', 'App\\Models\\') . $model;

    return new $model;
    //return Cuber\Foundation\Container::getInstance($model);
}

/**
 * 创建可写目录
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
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true)) {
                return false;
            }
        }
        return @chmod($dir, 0777);
    }

    return true;
}

/**
 * log
 *
 * @param string $file
 * @param string $data
 * @param string $mode
 *
 * @return bool
 */
function ll($file = '', $data = '', $mode = 'ab')
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

/**
 * 获取客户端IP
 *
 * @return string
 */
function get_client_ip()
{
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        } elseif (getenv($key)) {
            return getenv($key);
        }
    }

    return '127.0.0.1';
}

/**
 * 编码转换
 *
 * @param string $in_charset
 * @param string $out_charset
 * @param array|string $array
 *
 * @return array|string
 */
function iconv_array($in_charset, $out_charset, $array)
{
    if ('//IGNORE' != substr($out_charset, -8) and '//TRANSLIT' != substr($out_charset, -10)) {
        $out_charset .= '//IGNORE';
    }

    if (is_array($array)) {
        foreach ($array as $key=>$value) {
            unset($array[$key]);
            $key = iconv($in_charset, $out_charset, $key);
            $array[$key] = iconv_array($in_charset, $out_charset, $value);
        }
    } elseif (is_string($array)) {
        $array = iconv($in_charset, $out_charset, $array);
    }

    return $array;
}

/**
 * 将数组数字值转为字符串值
 *
 * @param array|string $data
 *
 * @return array|string
 */
function to_string($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = to_string($value);
        }
    } elseif (is_object($data)) {
    } else {
        $data = strval($data);
    }

    return $data;
}
