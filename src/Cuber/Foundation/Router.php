<?php

/**
 * Router
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Foundation\Route;
use Cuber\Config\Config;

class Router
{

    private static $_instance = null;

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 命中路由规则
     *
     * @return ['route'=>$route, 'controller'=>$controller, 'action'=>$action, 'closure'=>closure, 'closure_param'=>[]]
     */
    public function hitRoute()
    {
        $route = $this->getRouter();
        $rules = $this->getRule();

        // 是否命中路由规则
        foreach ($rules as $rule => $line) {
            $_rule = $this->regular($rule, $line['pattern']);

            if (preg_match('/^' . $_rule . '$/i', $route, $mat) or (')' == substr($_rule, -1) and preg_match('/^' . $_rule . '$/i', $route . '/', $mat))) {
                // $mat_param = ['{name}', '{id}']
                preg_match_all('/{[a-z]+}/', $rule, $mat_param, PREG_PATTERN_ORDER);
                isset($mat_param) and isset($mat_param[0]) and $mat_param = $mat_param[0];

                // $_param = ['{name}'=>'code', '{id}'=>10086]
                // $closure_param = ['name'=>'code', 'id'=>10086]
                $_param = $closure_param = [];
                foreach ($mat as $key => $value) {
                    if ($key > 0) {
                        $k = isset($mat_param[$key-1]) ? $mat_param[$key-1] : $key;
                        $_param[$k] = $value;
                        $closure_param[ substr($k, 1, -1) ] = $value;
                    }
                }

                if (is_string($line['rule'])) {
                    return array_merge(['route'=>$route], $this->makeControllerByRule(strtr($line['rule'], $_param)));
                } else {
                    return ['route'=>$route, 'closure'=>$line['rule'], 'closure_param'=>$closure_param];
                }
            }
        }
        return $this->makeControllerByRoute($route);
    }

    /**
     * 执行闭包路由
     *
     * @param closure $closure['closure']
     * @param array $closure['param']
     * @return bool
     */
    public function runClosureRoute($closure, $param = null)
    {
        if (!isset($closure) or is_string($closure) or !is_callable($closure)) {
            return false;
        }

        $param      = isset($param) ? $param : [];
        $call_value = []; // 闭包参数值
        $reflect    = new \ReflectionFunction($closure);

        foreach ($reflect->getParameters() as $_param) {
            $name = $_param->getName();

            if (isset($param[$name]) and ''!==$param[$name]) {
                $call_value[] = $param[$name];
            } else {
                if ($_param->isOptional()) {
                    $call_value[] = $_param->getDefaultValue();
                } else {
                    $call_value[] = null;
                }
            }
        }

        return call_user_func_array($closure, $call_value);
    }

    /**
     * 取路由规则
     *
     * @return array
     */
    private function getRule()
    {
        $routes = Route::getInstance()->getRoute();

        if (empty($routes)) {
            return [];
        }

        $host = $_SERVER['HTTP_HOST'];

        // 是否命中子域名组
        foreach ($routes as $domain => $rule) {
            if ('*' == $domain) {
                continue 1;
            }

            $domain = strtr($domain, Route::getInstance()->getPattern());
            if (preg_match('/^' . $domain . '$/i', $host)) {
                return $rule;
            }
        }

        if (isset($routes['*'])) {
            return $routes['*'];
        }

        return [];
    }

    /**
     * 将通配符转为正则
     *
     * @param string $rule
     * @param array $pattern
     * @return string
     */
    private function regular($rule = '', $pattern = [])
    {
        if (!isset($rule) or '' === $rule) {
            return '';
        }

        $_pattern = Route::getInstance()->getPattern();

        if (!empty($pattern) and is_array($pattern)) {
            foreach ($pattern as $key=>$value) {
                $_pattern['{' . $key . '}'] = '(' . $value . ')';
            }
        }

        return strtr(trim($rule, '/'), $_pattern);
    }

    /**
     * makeController
     *
     * @param string $route
     * @return array
     */
    private function makeControllerByRoute($route = null)
    {
        $r = explode('/', trim($route, '/'));

        $action = $r[count($r)-1];
        unset($r[count($r)-1]);

        $controller = '';
        if (!empty($r)) {
            foreach ($r as $_r) {
                $controller .= ucfirst(strtolower($_r)) . '\\';
            }
        }
        $controller = rtrim($controller, '\\');

        return ['route'=>$route, 'controller'=>$controller, 'action'=>$action];
    }

    /**
     * makeController
     *
     * @param string $rule
     * @return array
     */
    public function makeControllerByRule($rule = null)
    {
        $rule = explode('?', $rule);
        isset($rule[1]) and $this->setParam($rule[1]); // 设置GET

        $r = explode('@', trim($rule[0], '\\'));
        $action = isset($r[1]) ? $r[1] : '';

        return ['controller'=>trim($r[0], '\\'), 'action'=>trim($action, '/')];
    }

    /**
     * 设置GET
     *
     * @param string $url
     * @return bool
     */
    private function setParam($param = '')
    {
        if (empty($param)) {
            return true;
        }

        $get = explode('&', $param);
        foreach ($get as $_g) {
            $_g = explode('=', $_g);
            $_GET[$_g[0]] = isset($_g[1]) ? $_g[1] : '';
        }
        return true;
    }

    /**
     * getRouterByCli
     *
     * @return string
     */
    private function getRouterByCli()
    {
        return isset($GLOBALS['argv'][1]) ? trim($GLOBALS['argv'][1], '/') : '';
    }

    /**
     * getRouterByGet
     *
     * @return string
     */
    private function getRouterByGet()
    {
        $name = Config::get('route_get', 'r');

        return isset($_GET[$name]) ? trim($_GET[$name], '/') : '';
    }

    /**
     * getRouterByPathInfo
     *
     * @return string
     */
    private function getRouterByPathInfo()
    {
        return isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';
    }

    /**
     * getRouterByRequestUri
     *
     * @return string
     */
    private function getRouterByRequestUri()
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        $script_name = $_SERVER['SCRIPT_NAME'];

        if (substr($request_uri, 0, strlen($script_name)) == $script_name) {
            $route = substr($request_uri, strlen($script_name));
        } else {
            $route = substr($request_uri, strlen(dirname($script_name)));
        }

        $route = explode('?', $route);
        return trim($route[0], '/');
    }

    /**
     * 获取完整路由
     *
     * @return string
     */
    public function getRouter()
    {
        if (is_cli()) {
            return $this->getRouterByCli();
        }

        $url_model = Config::get('url_model', 1);
        if (2 == $url_model) {
            $_route = $this->getRouterByPathInfo();
        } elseif (3 == $url_model) {
            $_route = $this->getRouterByGet();
        } elseif (4 == $url_model) {
            $_route = Config::get('route_func') ? call_user_func(Config::get('route_func')) : \get_route();
        } else {
            $_route = $this->getRouterByRequestUri();
        }
        return $_route;
    }

    /**
     * load
     *
     * @param string $route
     *
     * @return void
     */
    public function load($route = 'app')
    {
        require BASE_PATH . '/route/' . $route . '.php';
    }

}
