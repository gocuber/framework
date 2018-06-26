<?php

/**
 * Url
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Url
{

    /**
     * 取url
     *
     * @see getUrl()
     * @return str $url
     */
    public static function get($module = null)
    {
        return self::getUrl($module);
    }

    /**
     * 取url
     *
     * @param str $module 模块 默认当前访问模块
     * @return str $url
     */
    public static function getUrl($module = null)
    {
        if(!isset($module)){
            $module = Module::getCurr();
        }

        $http     = self::isHttps() ? 'https://' : 'http://';
        $domain   = self::getDomain();
        $sitepath = self::getSitePath();

        // 如果此模块设置了独立域名
        if(isset($GLOBALS['_G']['module_domain'][$module])){
        	return $http . $GLOBALS['_G']['module_domain'][$module] . $sitepath . '/';
        }

        // 如果访问的默认模块
        if(Module::getDefault() == $module){
            return $http . $domain . $sitepath . '/';
        }
        return $http . $domain . $sitepath . '/' . $module . '/';
    }

    /**
     * 取当前模块url
     *
     * @return str $url
     */
    public static function getCurrUrl()
    {
        return self::getUrl(Module::getCurr());
    }

    /**
     * 取默认模块url 网站主站url
     *
     * @return str $url
     */
    public static function getDefaultUrl()
    {
        return self::getUrl(Module::getDefault());
    }

    /**
     * 取静态资源url
     *
     * @return str $url
     */
    public static function getResUrl()
    {
        if(self::isResDomain()){
            return 'http://'.self::getResDomain().'/res/';
        }else{
            return 'http://'.self::getResDomain().self::getSitePath().'/res/';
        }
    }

    /**
     * 取图库url
     *
     * @return str $url
     */
    public static function getImgUrl()
    {
        if(self::isImgDomain()){
            return 'http://'.self::getImgDomain().'/img/';
        }else{
            return 'http://'.self::getImgDomain().self::getSitePath().'/img/';
        }
    }

    /**
     * 取网站域名
     *
     * @return str $domain
     */
    private static function getDomain()
    {
        $domain = 'localhost';
        if(isset($GLOBALS['_G']['domain'])){
            $domain = $GLOBALS['_G']['domain'];
        }elseif(defined('DOMAIN')){
            $domain = DOMAIN;
        }elseif(isset($_SERVER['HTTP_HOST'])){
            $domain = $_SERVER['HTTP_HOST'];
        }
        return $domain;
    }

    /**
     * 取静态资源域名
     *
     * @return str $domain
     */
    private static function getResDomain()
    {
        $domain = 'localhost';
        if(isset($GLOBALS['_G']['res_domain'])){
            $domain = $GLOBALS['_G']['res_domain'];
        }elseif(defined('RES_DOMAIN')){
            $domain = RES_DOMAIN;
        }elseif(isset($_SERVER['HTTP_HOST'])){
            $domain = $_SERVER['HTTP_HOST'];
        }
        return $domain;
    }

    /**
     * 取图库域名
     *
     * @return str $domain
     */
    private static function getImgDomain()
    {
        $domain = 'localhost';
        if(isset($GLOBALS['_G']['img_domain'])){
            $domain = $GLOBALS['_G']['img_domain'];
        }elseif(defined('IMG_DOMAIN')){
            $domain = IMG_DOMAIN;
        }elseif(isset($_SERVER['HTTP_HOST'])){
            $domain = $_SERVER['HTTP_HOST'];
        }
        return $domain;
    }

    /**
     * 取网站相对于web根目录的路径部分
     *
     * @return string
     */
    private static function getSitePath()
    {
        $_site = dirname($_SERVER['SCRIPT_NAME']);
        $_site = (strlen($_site)>1) ? $_site : '';
        return $_site;
    }

    /**
     * isDomain
     *
     * @return boolean
     */
    public static function isDomain()
    {
        return isset($GLOBALS['_G']['domain']) or defined('DOMAIN');
    }

    /**
     * isResDomain
     *
     * @return boolean
     */
    public static function isResDomain()
    {
        return isset($GLOBALS['_G']['res_domain']) or defined('RES_DOMAIN');
    }

    /**
     * isImgDomain
     *
     * @return boolean
     */
    public static function isImgDomain()
    {
        return isset($GLOBALS['_G']['img_domain']) or defined('IMG_DOMAIN');
    }

    /**
     * isHttps
     *
     * @return boolean
     */
    public static function isHttps()
    {
    	return (
    	    (defined('IS_HTTPS') and IS_HTTPS == true) or
    	    (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') or
    	    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) and $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        );
    }

    /**
     * 取cookie域
     *
     * @return str $domain
     */
    public static function getCookieDomain()
    {
        if(isset($GLOBALS['_G']['cookie_domain'])){
            return $GLOBALS['_G']['cookie_domain'];
        }
        if(defined('COOKIE_DOMAIN')){
            return COOKIE_DOMAIN;
        }
        return null;
    }

}
