<?php

/**
 * View
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class View
{

    private static $_public_data   = null; // 公有数据 全局数据
    private static $_private_data  = null; // 私有数据 临时数据

    /**
     * set
     *
     * @see assign
     */
    public static function set($key = null, $value = null)
    {
        return self::assign($key, $value);
    }

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
        if(!isset($key)){
            return null;
        }

        if(is_scalar($key)){
            self::$_public_data[$key] = $value;
        }elseif(!empty($key) and is_array($key)){
            foreach($key as $k=>$v){
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
        if(!empty($data) and is_array($data)){
            self::$_private_data = $data;
        }
        return null;
    }

    /**
     * show
     *
     * @see display
     */
    public static function show($_tpl = null, $_data = null, $_dir = null)
    {
        return self::display($_tpl, $_data, $_dir);
    }

    /**
     * display
     *
     * @param string $_tpl
     * @param array $_data
     * @param string $_dir
     *
     * @return void
     */
    public static function display($_tpl = null, $_data = null, $_dir = null)
    {
        if(!isset($_dir)){
            $_dir = APP_DIR . 'views/';
        }

        if(!empty($_data) and is_array($_data)){
            self::assign($_data);
        }

        if(!empty(self::$_public_data) and is_array(self::$_public_data)){
            foreach(self::$_public_data as $key => $value){
                $$key = $value;
            }
        }
        if(!empty(self::$_private_data) and is_array(self::$_private_data)){
            foreach(self::$_private_data as $key => $value){
                $$key = $value;
            }
        }
        include $_dir . $_tpl . '.php';
    }

    /**
     * load
     *
     * @param string $tpl
     * @param array $private_data
     * @param string $dir
     *
     * @return void
     */
    public static function load($tpl = null, $private_data = null, $dir = null)
    {
        if(!empty($private_data) and is_array($private_data)){
            self::setPrivateData($private_data);
        }
        return self::display($tpl, null, $dir);
    }

}
