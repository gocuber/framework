<?php

/**
 * View
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Config\Config;

class View
{

    private static $_public_data  = null; // 公有数据 全局数据

    private static $_private_data = null; // 私有数据 临时数据

    /**
     * assign
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public static function assign($key = null, $value = null)
    {
        if (!isset($key)) {
            return null;
        }

        if (is_scalar($key)) {
            self::$_public_data[$key] = $value;
        } elseif (!empty($key) and is_array($key)) {
            foreach ($key as $k=>$v) {
                self::$_public_data[$k] = $v;
            }
        }

        return null;
    }

    /**
     * display
     *
     * @param string $_tpl
     * @param array $_data
     *
     * @return void
     */
    public static function display($_tpl = null, $_data = null)
    {
        if (!empty($_data) and is_array($_data)) {
            self::assign($_data);
        }

        foreach ([self::$_public_data, self::$_private_data] as $_item) {
            if (!empty($_item) and is_array($_item)) {
                foreach ($_item as $_key => $_value) {
                    $$_key = $_value;
                }
            }
        }

        include Config::get('views', base_path() . 'app/views/') . $_tpl . '.php';
    }

    /**
     * load
     *
     * @param string $tpl
     * @param array $data
     *
     * @return void
     */
    public static function load($tpl = null, $data = null)
    {
        if (!empty($data) and is_array($data)) {
            self::$_private_data = $data;
        }

        return self::display($tpl, null);
    }

}
