<?php

/**
 * Route
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class Route
{

    private $route = null;

    private $domain = '*';

    private $pattern = [];

    /**
     * 通配符
     *
     * @param array $pattern
     * @return bool
     */
    public function pattern(array $pattern = [])
    {
        $this->pattern = array_merge($this->pattern, $pattern);

        return true;
    }

    /**
     * 路由
     *
     * @param string $route
     * @param string $rule
     * @param array $pattern
     *
     * @return bool
     */
    public function get($route = '', $rule = null, $pattern = null)
    {
        if (!isset($route)) {
            return false;
        }

        $this->route[$this->domain][$route] = ['rule'=>$rule, 'pattern'=>$pattern];
        return true;
    }

    /**
     * 配置一组路由
     *
     * @return bool
     */
    public function group($group = [], $rule = null)
    {
        if (!isset($group['domain'])) {
            return false;
        }

        $this->domain = $group['domain'];
        $rule();
        $this->domain = '*';

        return true;
    }

    /**
     * 配置子域名路由
     *
     * @return bool
     */
    public function domain($domain = '', $rule = null)
    {
        return $this->group(['domain'=>$domain], $rule);
    }

    /**
     * 获取全局约束通配符
     *
     * @return array
     */
    public function getPattern()
    {
        $pattern = ['/'=>'\/'];

        foreach ($this->pattern as $key=>$value) {
            $pattern['{' . $key . '}'] = '(' . $value . ')';
        }

        return $pattern;
    }

    /**
     * 命中路由规则
     *
     * @return ['controller'=>$controller, 'action'=>$action, 'closure'=>closure, 'closure_param'=>[]]
     */
    public function hitRoute()
    {
        $route = $this->getRouter();
        $rules = $this->getRule();

        // 是否命中路由规则
        foreach ($rules as $rule => $line) {
            $_rule = $this->regular($rule, $line['pattern']);

            if (preg_match('/^' . $_rule . '$/i', $route, $mat)
                or (')' == substr($_rule, -1) and preg_match('/^' . $_rule . '$/i', $route . '/', $mat))) {

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
                    return $this->makeControllerByRule(strtr($line['rule'], $_param));
                } else {
                    return ['closure'=>$line['rule'], 'closure_param'=>$closure_param];
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
    public function runClosureRoute($closure, array $param = [])
    {
        if (!isset($closure) or is_string($closure) or !is_callable($closure)) {
            return false;
        }

        $values  = []; // 闭包参数值
        $reflect = new \ReflectionFunction($closure);

        foreach ($reflect->getParameters() as $_param) {
            $name = $_param->getName();

            if (isset($param[$name]) and '' !== $param[$name]) {
                $values[] = $param[$name];
            } else {
                $values[] = $_param->isOptional() ? $_param->getDefaultValue() : null;
            }
        }

        return call_user_func_array($closure, $values);
    }

    /**
     * 取路由规则
     *
     * @return array
     */
    private function getRule()
    {
        $routes = $this->route;

        if (empty($routes)) {
            return [];
        }

        $host = $_SERVER['HTTP_HOST'];

        // 是否命中子域名组
        foreach ($routes as $domain => $rule) {
            if ('*' == $domain) {
                continue 1;
            }

            $domain = strtr($domain, $this->getPattern());
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
        $rule = trim($rule, '/');
        if ('' === $rule) {
            return '';
        }

        $_pattern = $this->getPattern();

        if (!empty($pattern) and is_array($pattern)) {
            foreach ($pattern as $key=>$value) {
                $_pattern['{' . $key . '}'] = '(' . $value . ')';
            }
        }

        $rule = strtr($rule, $_pattern);
        $rule = preg_replace('/{[a-z]+}/', '(.+)', $rule); // 未设置匹配规则默认 .+
        return trim($rule, '/');
    }

    /**
     * makeController
     *
     * @param string $route sys/article/add
     *
     * @return array ['controller'=>'Sys\\Article', 'action'=>'add']
     */
    private function makeControllerByRoute($route = null)
    {
        $route = explode('/', $route);

        $action = count($route) == 1 ? '' : array_pop($route);

        $controller = [];
        if ($route) {
            foreach ($route as $r) {
                $controller[] = ucfirst(strtolower($r));
            }
        }
        $controller = implode('\\', $controller);

        return ['controller'=>$controller, 'action'=>$action];
    }

    /**
     * makeController
     *
     * @param string $rule Sys\\Article@index?id=1
     *
     * @return array ['controller'=>'Sys\\Article', 'action'=>'add']
     */
    public function makeControllerByRule($rule = null)
    {
        $rule = explode('?', $rule);
        isset($rule[1]) and $this->setParam($rule[1]); // 设置GET

        $rule = explode('@', $rule[0]);
        $action = isset($rule[1]) ? $rule[1] : '';

        return ['controller'=>$rule[0], 'action'=>$action];
    }

    /**
     * 设置GET
     *
     * @param string $url id=1&code=1
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
     * 获取完整路由
     *
     * @return string
     */
    public function getRouter()
    {
        if (is_cli()) {
            $route = isset($GLOBALS['argv'][1]) ? $GLOBALS['argv'][1] : '';
        } else {
            $request_uri = $_SERVER['REQUEST_URI'];
            $script_name = $_SERVER['SCRIPT_NAME'];

            if (substr($request_uri, 0, strlen($script_name)) == $script_name) {
                $route = substr($request_uri, strlen($script_name));
            } else {
                $route = substr($request_uri, strlen(dirname($script_name)));
            }

            $route = explode('?', $route);
            $route = $route[0];
        }

        $route = trim($route, '/');
        app()->bind('app.route', $route);
        return $route;
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
        require base_path('route/' . $route . '.php');
    }

}
