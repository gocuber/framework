<?php

/**
 * Url
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Support;

class Url
{

    /**
     * 取url
     *
     * @param string $url
     *
     * @return string $url
     */
    public function getUrl($url = '')
    {
        return $url;
    }

    /**
     * 模块url
     *
     * @param string $module
     * @param string $url
     * @param string $http
     *
     * @return string $url
     */
    public function getModuleUrl($module = '', $url = '', $http = '//')
    {
        return $http . config('module.' . $module . '.domain', '') . $url;
    }

    /**
     * 域名url
     *
     * @param string $domain
     * @param string $url
     * @param string $http
     *
     * @return string $url
     */
    public function getDomainUrl($domain = '', $url = '', $http = '//')
    {
        return $http . $domain . $url;
    }

    /**
     * res url
     *
     * @param string $url
     * @param string $domain
     * @param string $http
     *
     * @return string $url
     */
    public function getResUrl($url = '', $domain = '', $http = '//')
    {
        if (isset($domain) and '' !== $domain) {
            return $http . $domain . $url;
        } else {
            if (config('res_domain', false) and '' !== config('res_domain')) {
                return $http . config('res_domain') . $url;
            } else {
                return $url;
            }
        }
    }

}
