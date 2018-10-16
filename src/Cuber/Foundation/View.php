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
     * setPrivateData
     *
     * @param array $data
     *
     * @return void
     */
    private static function setPrivateData($data = null)
    {
        if (!empty($data) and is_array($data)) {
            self::$_private_data = $data;
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

        if (!empty(self::$_public_data) and is_array(self::$_public_data)) {
            foreach (self::$_public_data as $key => $value) {
                $$key = $value;
            }
        }
        if (!empty(self::$_private_data) and is_array(self::$_private_data)) {
            foreach (self::$_private_data as $key => $value) {
                $$key = $value;
            }
        }

        include Config::get('views', BASE_PATH . 'app/views/') . $_tpl . '.php';
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
        self::setPrivateData($data);

        return self::display($tpl, null);
    }

}
