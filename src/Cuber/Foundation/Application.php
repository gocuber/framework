<?php

/**
 * Cuber
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Support\Exception;

class Application extends Container
{

    /**
     * Create application instance
     *
     * @param string $base_path
     * @return void
     */
    public function __construct($base_path = '')
    {
        static::setInstance($this);

        $this->bind('app', $this);

        $this->bind('app.base_path', rtrim($base_path, '/') . '/');

        $this->registerBaseServices();

        $this->init();

        $this->registerBaseProviders();

        $this->registerBaseAliases();
    }

    /**
     * Run
     *
     * @return void
     */
    public function run()
    {
        $this->setModule()->setAction()->runAction();
    }

    /**
     * Set module
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

        $this->bind('app.module', $module_name);

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
     * Set action
     *
     * @return this
     */
    private function setAction()
    {
        $this->make('route')->load(config('module.' . app('app.module') . '.route', 'app'));
        $ret = $this->make('route')->hitRoute();

        if (isset($ret['closure'])) {
            $ret = $this->make('route')->runClosureRoute($ret['closure'], $ret['closure_param']);

            if (isset($ret) and is_string($ret)) {
                $ret = $this->make('route')->makeControllerByRule($ret);
            }
        }

        if (isset($ret) and is_array($ret)) {
            (!isset($ret['controller']) or '' === $ret['controller']) and $ret['controller'] = 'Index';
            (!isset($ret['action']) or '' === $ret['action'])         and $ret['action']     = 'index';
            foreach ($ret as $key=>$value) {
                $this->bind('app.' . $key, $value);
            }
        }

        return $this;
    }

    /**
     * Run action
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

    /**
     * Register base services
     *
     * @return void
     */
    protected function registerBaseServices()
    {
        foreach ([
            ['config', \Cuber\Config\Config::class, true],
        ] as $value) {
            $this->bind($value[0], function () use ($value) {
                return new $value[1];
            }, $value[2]);
        }
    }

    /**
     * init
     *
     * @return void
     */
    protected function init()
    {
        put_env();

        if (config('debug')) {
            ini_set('display_errors', 'on');
            error_reporting(-1);
        } else {
            ini_set('display_errors', 'off');
            error_reporting(0);
        }

        date_default_timezone_set(config('timezone'));
        header("Content-type: text/html; charset=" . config('charset'));
    }

    /**
     * Register base service providers
     *
     * @return void
     */
    protected function registerBaseProviders()
    {
        $providers = config('providers', []);

        if (!empty($providers) and is_array($providers)) {
            foreach ($providers as $provider) {
                (new $provider())->register();
            }
        }
    }

    /**
     * Register base aliases
     *
     * @return void
     */
    protected function registerBaseAliases()
    {
        $this->make('aliasloader', [config('aliases', [])])->register();
    }

}
