<?php

/**
 * Cuber
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Config\Config;
use Cuber\Support\Exception;

class Application
{

    public function __construct($base_path = '')
    {
        $this->init($base_path);
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
        app(['module' => Module::get()]);

        // controllers namespace prefix
        $namespace = Config::get('module.' . app('module') . '.controllers', '');
        if ('' !== $namespace) {
            Config::set('controllers_namespace', $namespace);
        }

        // views dir
        $views = Config::get('module.' . app('module') . '.views', '');
        if ('' !== $views) {
            Config::set('views', $views);
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
        $router = new Router();
        $router->load(Config::get('module.' . app('module') . '.route', 'app'));
        $ret = $router->hitRoute();

        if (isset($ret['closure'])) {
            $ret = $router->runClosureRoute($ret['closure'], $ret['closure_param']);

            if (isset($ret) and is_string($ret)) {
                $ret = $router->makeControllerByRule($ret);
            }
        }
        unset($router);

        if (isset($ret) and is_array($ret)) {
            (!isset($ret['controller']) or '' === $ret['controller']) and $ret['controller'] = 'Index';
            (!isset($ret['action']) or '' === $ret['action'])         and $ret['action']     = 'index';
            app($ret);
        }
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

            if (app('controller')) {

                $controller = app('controller');
                $action     = app('action');

                $c = Config::get('controllers_namespace', 'App\\Controllers\\') . $controller;
                if (is_callable([$c, $action])) {
                    (new $c())->$action();
                } else {
                    throw new Exception("Action '{$action}' not found");
                }
            }

        } catch (Exception $e) {

            $e->log();
            Config::debug() or ret404();

        }
    }

    /**
     * init
     *
     * @return void
     */
    private function init($base_path = '')
    {
        app(['base_path' => rtrim($base_path, '/') . '/']);
        put_env();

        if (Config::debug()) {
            ini_set('display_errors', 'on');
            error_reporting(-1);
        } else {
            ini_set('display_errors', 'off');
            error_reporting(0);
        }

        date_default_timezone_set(Config::timezone());
        header("Content-type: text/html; charset=" . Config::charset());

        is_cli() and app(['argv' => get_argv()]);
        (new AliasLoader())->register();
    }

}
