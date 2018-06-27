<?php

/**
 * Cube
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Cube
{

    private $_route = null;

    private $_controller = null;

    private $_action = null;

    private $_closure = null;

    private $_closure_param = null;

    private static $_instance = null;

    public static function G($class = 'Cube')
    {
        if(!isset(self::$_instance[$class])){
            self::$_instance[$class] = new $class();
        }
        return self::$_instance[$class];
    }

    /**
     * run
     *
     * @return void
     */
    public static function run()
    {
        Cube::G()->setAction()->runAction();
    }

    /**
     * setAction
     *
     * @return this
     */
    private function setAction()
    {
        $ret = Route::getInstance()->hitRoute();

        isset($ret['route'])         and $this->_route         = $ret['route'];
        isset($ret['controller'])    and $this->_controller    = $ret['controller'];
        isset($ret['action'])        and $this->_action        = $ret['action'];
        isset($ret['closure'])       and $this->_closure       = $ret['closure'];
        isset($ret['closure_param']) and $this->_closure_param = $ret['closure_param'];

        return $this;
    }

    /**
     * runAction
     *
     * @return void
     */
    private function runAction()
    {
        try {

            if(isset($this->_controller)){
                $route      = (isset($this->_route)      and '' !== $this->_route)      ? $this->_route      : '/';
                $controller = (isset($this->_controller) and '' !== $this->_controller) ? $this->_controller : 'Index';
                $action     = (isset($this->_action)     and '' !== $this->_action)     ? $this->_action     : 'index';

                $file = APP_DIR . 'controllers/' . $controller . '.php';
                if(!is_file($file) or !include_once($file)){
                    throw new CubeException("Controller '{$controller}' not found");
                }

                $c = 'controllers\\' . $controller;
                if(is_callable(array($c, $action))){
                    $class = new $c(['_route'=>$route, '_controller'=>$controller, '_action'=>$action]);
                    $class->$action();
                }else{
                    throw new CubeException("Action '{$action}' not found");
                }
            }else{
                Route::getInstance()->runClosureRoute($this->_closure, $this->_closure_param);
            }

        } catch (CubeException $e) {

            if(defined('APP_DEBUG') and APP_DEBUG){
                $e->log();
            }else{
                Util_App::ret404();
            }

        }
    }

}