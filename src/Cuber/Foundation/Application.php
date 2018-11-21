<?php

/**
 * Cuber
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Config\Config;
use Cuber\Support\Exception;
use Cuber\Support\Facades\Route;

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
        $module_name = 'default';

        if (is_cli()) {
            $module_name = 'cli';
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

        app(['module' => $module_name]);

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
        Route::load(Config::get('module.' . app('module') . '.route', 'app'));
        $ret = Route::hitRoute();

        if (isset($ret['closure'])) {
            $ret = Route::runClosureRoute($ret['closure'], $ret['closure_param']);

            if (isset($ret) and is_string($ret)) {
                $ret = Route::makeControllerByRule($ret);
            }
        }

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
                    throw new Exception("'$c@$action' not found");
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
