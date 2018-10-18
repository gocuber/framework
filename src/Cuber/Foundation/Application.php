<?php

/**
 * Cuber
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Support\Exception;
use Cuber\Config\Config;

class Application
{

    private $_base_path = null;

    private $_route = null;

    private $_module = 'default';

    private $_controller = null;

    private $_action = null;

    private $_closure = null;

    private $_closure_param = null;

    public function __construct($base_path = '')
    {
        $this->_base_path = $base_path;

        $this->init();
    }

    /**
     * run
     *
     * @return void
     */
    public function run()
    {
        $this->setModule()->setAction()->runAction();
    }

    /**
     * setModule
     *
     * @return $this
     */
    private function setModule()
    {
        $this->_module = Module::get();
        $namespace = Config::get('module.' . $this->_module . '.namespace', '');
        if ('' !== $namespace) {
            Config::set('controllers_namespace', $namespace);
        }
        return $this;
    }

    /**
     * setAction
     *
     * @return this
     */
    private function setAction()
    {
        Router::getInstance()->load(Config::get('module.' . $this->_module . '.route', 'app'));

        $ret = Router::getInstance()->hitRoute();

        isset($ret['route']) and $this->_route = $ret['route'];

        if (isset($ret['closure'])) {
            $ret = Router::getInstance()->runClosureRoute($ret['closure'], $ret['closure_param']);

            if (isset($ret) and false !== $ret and is_string($ret)) {
                $ret = Router::getInstance()->makeControllerByRule($ret);
            }
        }

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

            if (isset($this->_controller)) {

                $route      = (isset($this->_route)      and '' !== $this->_route)      ? $this->_route      : '/';
                $controller = (isset($this->_controller) and '' !== $this->_controller) ? $this->_controller : 'Index';
                $action     = (isset($this->_action)     and '' !== $this->_action)     ? $this->_action     : 'index';

                $c = Config::get('controllers_namespace', 'App\\Controllers\\') . $controller;
                if (is_callable([$c, $action])) {
                    $ctl = new $c(['_route'=>$route, '_controller'=>$controller, '_action'=>$action]);
                    $ctl->$action();
                } else {
                    throw new Exception("Action '{$action}' not found");
                }

            }

        } catch (Exception $e) {
            $e->log();
            if (!Config::debug()) {
                \ret404();
            }
        }
    }

    /**
     * basePath
     *
     * @return string
     */
    private function basePath()
    {
        return $this->_base_path;
    }

    /**
     * Init
     *
     * @return void
     */
    private function init()
    {
        define('BASE_PATH', $this->_base_path);

        $config = BASE_PATH . 'config/config.ini';
        if (is_file($config)) {
            $conf = parse_ini_file($config, false);
            if (!empty($conf) and is_array($conf)) {
                foreach ($conf as $key=>$value) {
                    \putenv("{$key}={$value}");
                }
            }
        }

        if (Config::debug()) {
            ini_set('display_errors', 'on');
            error_reporting(-1);
        } else {
            ini_set('display_errors', 'off');
            error_reporting(0);
        }

        date_default_timezone_set(Config::timezone());
        header("Content-type: text/html; charset=" . Config::charset());

        (new AliasLoader())->register();
    }

}
