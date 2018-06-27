<?php

/**
 * Util_Ob
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Util_Ob
{

    /**
     * 打开输出控制缓冲
     *
     * @return bool
     */
    public static function start()
    {
        return ob_start();
    }

    /**
     * 返回输出缓冲区的内容
     *
     * @return string
     */
    public static function getContents()
    {
        return ob_get_contents();
    }

    /**
     * 清空（擦掉）输出缓冲区
     *
     * @return void
     */
    public static function clean()
    {
        return ob_clean();
    }

    /**
     * 清空（擦除）缓冲区并关闭输出缓冲
     *
     * @return bool
     */
    public static function endClean()
    {
        return ob_end_clean();
    }

    /**
     * 得到当前缓冲区的内容并删除当前输出缓 实质上是一起执行了 ob_get_contents() 和 ob_end_clean()
     *
     * @return string
     */
    public static function getClean()
    {
        return ob_get_clean();
    }

}
