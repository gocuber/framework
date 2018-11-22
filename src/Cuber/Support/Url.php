<?php

/**
 * Url
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Support;

use Cuber\Support\Facades\Config;

class Url
{

    /**
     * 取url
     *
     * @param str $module 模块
     *
     * @return str $url
     */
    public static function getUrl($module = null)
    {

        $http     = self::isHttps() ? 'https://' : 'http://';
        $sitepath = self::getSitePath();
        $domain   = self::getDomain();

        if (isset($module)) {

            $module_domain = Config::moduleDomain($module);

            if (empty($module_domain)) {
                return $http . $domain . $sitepath . '/' . $module . '/';
            } else {
                return $http . $module_domain . '/';
            }

        } else {

            return $http . $domain . $sitepath . '/';

        }

    }

    /**
     * 取静态资源url
     *
     * @return str $url
     */
    public static function getResUrl()
    {
        $domain = Config::domain('res');

        if (!empty($domain)) {
            return 'http://' . $domain . '/res/';
        } else {
            return 'http://' . self::getDomain() . self::getSitePath() . '/res/';
        }
    }

    /**
     * 取图库url
     *
     * @return str $url
     */
    public static function getImgUrl()
    {
        $domain = Config::domain('img');

        if (!empty($domain)) {
            return 'http://' . $domain . '/img/';
        } else {
            return 'http://' . self::getDomain() . self::getSitePath() . '/img/';
        }
    }

    /**
     * 取网站域名
     *
     * @return str $domain
     */
    private static function getDomain()
    {
        $domain = Config::domain();

        if (empty($domain) and isset($_SERVER['HTTP_HOST'])) {
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
        $domain = Config::domain('res');

        if (empty($domain)) {
            return self::getDomain();
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
        $domain = Config::domain('img');

        if (empty($domain)) {
            return self::getDomain();
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
     * @return str|null
     */
    public static function getCookieDomain()
    {
        $domain = Config::domain('cookie');

        if (empty($domain)) {
            return null;
        }

        return $domain;
    }

}
