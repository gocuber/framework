<?php

/**
 * Module
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Config\Config;

class Module
{

    /**
     * 取当前模块
     *
     * @return string
     */
    public static function get()
    {
        $module_name = 'default';

        if (\is_cli()) {
            $module_name = 'cron';
        } else {
            $module_conf = Config::get('module');
            if (!empty($module_conf) and is_array($module_conf)) {
                $domain = $_SERVER['HTTP_HOST'];
                foreach ($module_conf as $module=>$conf) {
                    if (isset($conf['domain']) and $domain == $conf['domain']) {
                        $module_name = $module;
                        break 1;
                    }
                }
            }
        }

        return $module_name;
    }

}
