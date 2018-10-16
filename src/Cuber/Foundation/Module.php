<?php

/**
 * Module
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class Module
{

    /**
     * 取全部模块
     *
     * @return array
     */
    public static function getList()
    {}

    /**
     * 取默认模块
     *
     * @return string
     */
    public static function getDefault()
    {}

    /**
     * 取当前访问的模块
     *
     * @return string
     */
    public static function getCurr()
    {}

    /**
     * 取模块目录
     *
     * @param string $module
     * @return string
     */
    public static function getModuleDir($module = null)
    {}

    /**
     * 取模块控制器目录
     *
     * @param string $module
     * @return string
     */
    public static function getControllerDir($module = null)
    {}

    /**
     * 取模块视图目录
     *
     * @param string $module
     * @return string
     */
    public static function getViewDir($module = null)
    {}

}
