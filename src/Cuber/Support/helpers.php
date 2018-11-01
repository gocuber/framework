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

if (! function_exists('debug')) {
    /**
     * debug
     *
     * @return void
     */
    function debug()
    {
        if (isset($_GET['debug']) and func_get_arg(0) == $_GET['debug']) {
            s(func_get_args());
        }

        isset($_GET['_exit']) and exit();
    }
}

if (! function_exists('app')) {
    /**
     * app
     *
     * @param string|array $key
     * @param string|array $default
     * @return string|array
     */
    function app($key = null, $default = null)
    {
        if (null === $key or '' === $key) {
            return Cuber\Foundation\Container::getInstance();
        }

        if (is_array($key)) {
            return Cuber\Foundation\Container::getInstance()->set($key);
        }

        return Cuber\Foundation\Container::getInstance()->get($key, $default);
    }
}

if (! function_exists('view')) {
    /**
     * view
     *
     * @param string $tpl
     * @param array $data
     * @return Cuber\Support\Facades\View::display
     */
    function view($tpl = null, $data = null)
    {
        return Cuber\Support\Facades\View::display($tpl, $data);
    }
}

if (! function_exists('request')) {
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
        if (null === $key or '' === $key) {
            return Cuber\Foundation\Container::getInstance('Cuber\\Support\\Request');
        }

        if (in_array($type, ['get', 'post', 'request', 'argv'])) {
            return Cuber\Foundation\Container::getInstance('Cuber\\Support\\Request')->$type($key, $default);
        }
    }
}

if (! function_exists('config')) {
    /**
     * config
     */
    function config()
    {}
}

if (! function_exists('base_path')) {
    /**
     * base_path
     *
     * @return string
     */
    function base_path()
    {
        return app('base_path');
    }
}

if (! function_exists('array_get')) {
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
}

if (! function_exists('put_env')) {
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
}

if (! function_exists('get_client_ip')) {
    /**
     * 获取客户端IP
     *
     * @return string
     */
    function get_client_ip()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = '127.0.0.1';
        }

        return $ip;
    }
}

if (! function_exists('iconv_array')) {
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
        } elseif (isset($array) and is_string($array)) {
            $array = iconv($in_charset, $out_charset, $array);
        }

        return $array;
    }
}

if (! function_exists('to_string')) {
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
}

if (! function_exists('get_route')) {
    /**
     * 获取路由
     *
     * @return string
     */
    function get_route()
    {
        return isset($_GET['r']) ? $_GET['r'] : '';
    }
}
