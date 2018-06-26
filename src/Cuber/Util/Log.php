<?php

/**
 * Util_Log 日志类
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Util_Log
{

    /**
     * 覆盖写入
     *
     * @param string $file
     * @param string $data
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
     * @return bool
     */
    private static function set($file = '', $data = '', $mode = 'wb')
	{
		if(empty($file)){
			return false;
		}
		if(!self::isMkdir(dirname($file))){
			return false;
		}

		$handle = fopen($file, $mode);
		fwrite($handle, $data);
		fclose($handle);
		return true;
	}

	/**
	 * 创建目录
	 *
	 * @param string $dir
	 * @return bool
	 */
	private static function isMkdir($dir = null)
	{
		if(empty($dir)){
			return false;
		}
		if(!is_writable($dir)){
			if(!@mkdir($dir,0777,true)){
				return false;
			}
		}
        //@chmod($dir,0777);
		return true;
	}

}
