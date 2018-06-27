<?php

/**
 * Cuber
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Foundation\Route;
use Cuber\Support\Exception;

class Application
{

    private $_base_path = null;

    private $_route = null;

    private $_controller = null;

    private $_action = null;

    private $_closure = null;

    private $_closure_param = null;

    public function __construct($base_path = '')
    {
    	$this->_base_path = $base_path;
    	define('BASE_PATH', $base_path);
    	define('APP_DIR', BASE_PATH . 'app/');
    }

    /**
     * run
     *
     * @return void
     */
    public function run()
    {
        $this->setAction()->runAction();
    }

    /**
     * setAction
     *
     * @return this
     */
    private function setAction()
    {
        $ret = Route::getInstance()->hitRoute();
        s($ret);

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

                $file = $this->appPath() . 'controllers/' . $controller . '.php';
                if(!is_file($file) or !include_once($file)){
                    throw new Exception("Controller '{$controller}' not found");
                }

                $c = 'App\Controllers\\' . $controller;
                if(is_callable(array($c, $action))){
                    $class = new $c(['_route'=>$route, '_controller'=>$controller, '_action'=>$action]);
                    $class->$action();
                }else{
                    throw new Exception("Action '{$action}' not found");
                }
            }else{
                Route::getInstance()->runClosureRoute($this->_closure, $this->_closure_param);
            }

        } catch (Exception $e) {

            //if(defined('APP_DEBUG') and APP_DEBUG){
                $e->log();
            //}else{
            //    Util_App::ret404();
            //}

        }
    }

    /**
     * basePath
     *
     * @return string
     */
    public function basePath()
    {
    	return $this->_base_path;
    }

    /**
     * appPath
     *
     * @return string
     */
    public function appPath()
    {
    	return $this->_base_path . 'app/';
    }

}
