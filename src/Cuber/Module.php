<?php

/**
 * Module
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Module
{

    /**
     * 取全部模块
     *
     * @return array
     */
    public static function getList()
    {
        return empty($GLOBALS['_G']['module_list']) ? array() : $GLOBALS['_G']['module_list'];
    }

    /**
     * 取默认模块
     *
     * @return string
     */
    public static function getDefault()
    {
        return isset($GLOBALS['_G']['module_default']) ? $GLOBALS['_G']['module_default'] : 'default';
    }

    /**
     * 取当前访问的模块
     *
     * @return string
     */
    public static function getCurr()
    {
        return defined('MODULE_NAME') ? MODULE_NAME : self::getDefault();
    }

    /**
     * 取模块目录
     *
     * @param string $module
     * @return string
     */
    public static function getModuleDir($module = null)
    {
        return APP_DIR;
        // !isset($module) and $module = self::getDefault();
        // return isset($GLOBALS['_G']['module_dir'][$module]) ? $GLOBALS['_G']['module_dir'][$module] : APP_DIR . $module . '/';
    }

    /**
     * 取模块控制器目录
     *
     * @param string $module
     * @return string
     */
    public static function getControllerDir($module = null)
    {
        return self::getModuleDir($module) . 'controllers/';
    }

    /**
     * 取模块视图目录
     *
     * @param string $module
     * @return string
     */
    public static function getViewDir($module = null)
    {
        return self::getModuleDir($module) . 'views/';
    }

    /**
     * 根据请求的域名获取配置子域名的模块
     *
     * @param string $domain
     * @return string|false
     */
    public static function getModuleByDomain($domain = null)
    {
        isset($domain) or $domain = $_SERVER['HTTP_HOST'];
        if(empty($GLOBALS['_G']['module_domain']) or !is_array($GLOBALS['_G']['module_domain'])){
            return false;
        }
        return array_search($domain, $GLOBALS['_G']['module_domain']);
    }

}
