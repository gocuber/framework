<?php

/**
 * Exception
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Support;

class Exception extends \Exception
{

    const ERROR_TYPE_MYSQL   = 'mysql';
    const ERROR_TYPE_REDIS   = 'redis';
    const ERROR_TYPE_MEM     = 'memcache';
    const ERROR_TYPE_FC      = 'filecache';
    const ERROR_TYPE_ES      = 'elasticsearch';
    const ERROR_TYPE_SYSTEM  = 'system';
    const ERROR_TYPE_APP     = 'app';

    /**
     * log
     *
     * @param string $type
     * @param Exception $e
     * @param bool $exit
     *
     * @return void
     */
    public function log($type = self::ERROR_TYPE_APP, $e = null, $exit = false)
    {
        if ($e instanceof \Exception) {
            $msg   = $e->getMessage();
            $trace = $e->getTrace();
        } else {
            $msg   = $this->getMessage();
            $trace = $this->getTrace();
        }

        $str = '';
        if (!empty($trace) and is_array($trace)) {
            foreach ($trace as $key=>$value) {
                $str .= '#' . $key . ' ';

                isset($value['class'])    and $str .= $value['class'];
                isset($value['type'])     and $str .= $value['type'];
                isset($value['function']) and $str .= $value['function'];
                isset($value['args'])     and $str .= '('. strtr(print_r($value['args'], true), ["\n"=>'', "\r"=>'', '  '=>'']) .'); ';
                isset($value['file'])     and $str .= $value['file'];
                isset($value['line'])     and $str .= ' ' . $value['line'];

                $str .= "\n";
            }
        }

        if (config('debug')) {
            echo '<pre>' . $msg . "\n" . $str . '</pre>';
        }

        if (!config('error_log')) {
            $error_log = config('error_log');
            $cli       = is_cli() ? '_cli' : '';
            $file      = date('Ymd') . '_' . $type . $cli . '_error.log';
            file_log($error_log . $file, date('Y-m-d H:i:s') . " ------------------------------------------\n{$msg}\n{$str}\n");
        }

        $exit and exit();
    }

}
