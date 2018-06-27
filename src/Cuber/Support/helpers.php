<?php

/**
 * helpers
 *
 * @author Cuber <dafei.net@gmail.com>
 */

class Util_App
{

    /**
     * 是否CLI运行
     *
     * @return bool
     */
    public static function isCli()
    {
        if(defined('IS_CLI')){
        	return IS_CLI;
        }
        return ('cli' === php_sapi_name());
    }

    /**
     * header 404
     *
     * @return void
     */
    public static function ret404()
    {
        header('HTTP/1.1 404 Not Found');
    }

    /**
     * getArgv
     *
     * @return array
     */
    public static function getArgv()
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
