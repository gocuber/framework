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
 * @param null|string $key
 * @param mixed $default
 *
 * @return mixed
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
 * @return mixed
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
 * @param null|string $key
 * @param mixed $default
 *
 * @return mixed
 */
function session($key = null, $default = null)
{
    if (is_null($key)) {
        return app('session');
    }

    return app('session')->get($key, $default);
}

/**
 * config
 *
 * @param string $key
 * @param string|array $default
 *
 * @return string|array
 */
function config($key = null, $default = null)
{
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
 * res_url
 *
 * @param string $url
 *
 * @return string
 */
function res_url($url = '')
{
    return url($url, config('res_domain'));
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
 * array_hash
 *
 * @param array $array
 * @param string $key_field
 * @param string|array $value_field
 *
 * @return array
 */
function array_hash($array = [], $key_field = 'id', $value_field = null)
{
    if (empty($array) or !is_array($array)) {
        return [];
    }

    $hash = [];
    foreach ($array as $value) {
        $hash[$value[$key_field]] = is_scalar($value_field) ? $value[$value_field] : $value;
    }

    if (is_array($value_field)) {
        return array_field($hash, $value_field);
    }

    return $hash;
}

/**
 * array_field
 *
 * @param array $array
 * @param string|array $fields
 *
 * @return array
 */
function array_field($array = [], $fields = null)
{
    if (empty($array) or !is_array($array) or empty($fields)) {
        return [];
    }

    $fields = is_array($fields) ? $fields : explode(',', $fields);
    $new = [];
    foreach ($array as $key=>$value) {
        $line = [];
        foreach ($fields as $field) {
            $line[$field] = isset($value[$field]) ? $value[$field] : '';
        }
        $new[$key] = $line;
    }

    return $new;
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
        } else {
            return @chmod($dir, 0777);
        }
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
