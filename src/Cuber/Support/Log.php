<?php

/**
 * Log
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Support;

class Log
{

    /**
     * 覆盖写入
     *
     * @param string $file
     * @param string $data
     *
     * @return bool
     */
    public static function write($file = '', $data = '')
    {
        return self::set($file, $data, 'wb');
    }

    /**
     * 追加写入
     *
     * @param string $file
     * @param string $data
     *
     * @return bool
     */
    public static function add($file = '', $data = '')
    {
        return self::set($file, $data, 'ab');
    }

    /**
     * 写入
     *
     * @param string $file
     * @param string $data
     * @param string $mode
     *
     * @return bool
     */
    private static function set($file = '', $data = '', $mode = 'wb')
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

}
