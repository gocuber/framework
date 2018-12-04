<?php

/**
 * Cuber
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Support\Exception;
use Cuber\Support\Facades\Route;
use Cuber\Foundation\Container;

class Application extends Container
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
            $module_conf = config('module');
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

        app()->bind('app.module', $module_name);

        // controllers namespace prefix
        $namespace = config('module.' . app('app.module') . '.controllers', '');
        if ('' !== $namespace) {
            config(['controllers_namespace'=>$namespace]);
        }

        // views dir
        $views = config('module.' . app('app.module') . '.views', '');
        if ('' !== $views) {
            config(['views'=>$views]);
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
        Route::load(config('module.' . app('app.module') . '.route', 'app'));
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
            foreach ($ret as $key=>$value) {
                app()->bind('app.' . $key, $value);
            }
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

            if (app('app.controller')) {

                $controller = app('app.controller');
                $action     = app('app.action');

                $c = config('controllers_namespace', 'App\\Controllers\\') . $controller;
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

    private function register()
    {
        foreach ([
            ['app', \Cuber\Foundation\App::class, true],
            ['config', \Cuber\Config\Config::class, true],
            ['route', \Cuber\Foundation\Route::class, true],
            ['request', \Cuber\Support\Request::class, true],
            ['cookie', \Cuber\Support\Cookie::class, true],
            ['session', \Cuber\Support\Session::class, true],
            ['view', \Cuber\Support\View::class, true],
            ['db', \Cuber\Support\DB::class, false],
            ['redis', \Cuber\Redis\Redis::class, false],
            ['url', \Cuber\Support\Url::class, true],
            ['memcache', \Cuber\Support\Memcache::class, true],
            ['filecache', \Cuber\Support\Filecache::class, true],
        ] as $value) {
            Container::getInstance()->bind($value[0], function () use ($value) {
                return new $value[1];
            }, $value[2]);
        }
    }

    /**
     * init
     *
     * @return void
     */
    private function init($base_path = '')
    {
        app()->bind('app.base_path', rtrim($base_path, '/') . '/');
        put_env();
        $this->register();

        if (config('debug')) {
            ini_set('display_errors', 'on');
            error_reporting(-1);
        } else {
            ini_set('display_errors', 'off');
            error_reporting(0);
        }

        date_default_timezone_set(config('timezone'));
        header("Content-type: text/html; charset=" . config('charset'));

        is_cli() and app()->bind('app.argv', get_argv());
        (new AliasLoader())->register();
    }

}
