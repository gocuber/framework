<?php

/**
 * Route
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Route
{

    private static $_instance = null;

    private $_routes = null;

    private $_domain = '*';

    private $_wildcard = [];

    public static function getInstance()
    {
        if(!isset(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 通配符
     *
     * @param array $wildcard
     * @return bool
     */
    public static function wildcard($wildcard = [])
    {
        return self::getInstance()->addWildcard($wildcard);
    }

    /**
     * 路由
     *
     * @return bool
     */
    public static function get($route = '', $rule = null, $wildcard = null)
    {
        return self::getInstance()->set($route, $rule, $wildcard);
    }

    /**
     * 配置一组路由
     *
     * @return bool
     */
    public static function group($group = [], $rule = null)
    {
        if(!isset($group['domain'])){
        	return false;
        }

        $r = self::getInstance();
        $r->_domain = $group['domain'];
        $rule();
        $r->_domain = '*';
        unset($r);
        return true;
    }

    /**
     * 配置子域名路由
     *
     * @return bool
     */
    public static function domain($domain = '', $rule = null)
    {
        return self::group(['domain'=>$domain], $rule);
    }

    /**
     * 命中路由规则
     *
     * @return ['route'=>$route, 'controller'=>$controller, 'action'=>$action, 'closure'=>closure, 'closure_param'=>[]]
     */
    public function hitRoute()
    {
        $route = $this->getRoute();
        $rules = $this->getRule();

        // 是否命中路由规则
        foreach($rules as $rule => $line){

            $_rule = $this->getPattern($rule, $line['wildcard']);

            if(preg_match('/^' . $_rule . '$/i', $route, $mat) or (')' == substr($_rule, -1) and preg_match('/^' . $_rule . '$/i', $route . '/', $mat))){

                // $mat_param = ['{name}', '{id}']
                preg_match_all('/{[a-z]+}/', $rule, $mat_param, PREG_PATTERN_ORDER);
                isset($mat_param) and isset($mat_param[0]) and $mat_param = $mat_param[0];

                // $_param = ['{name}'=>'code', '{id}'=>10086]
                // $closure_param = ['name'=>'code', 'id'=>10086]
                $_param = $closure_param = [];
                foreach($mat as $key => $value){
                    if($key > 0){
                        $k = isset($mat_param[$key-1]) ? $mat_param[$key-1] : $key;
                        $_param[$k] = $value;
                        $closure_param[ substr($k, 1, -1) ] = $value;
                    }
                }

                if(is_string($line['rule'])){
                    $_r = explode('?', $line['rule']);
                    isset($_r[1]) and $this->setParam(strtr($_r[1], $_param)); // 设置GET

                    return array_merge(['route'=>$route], $this->makeControllerByRule($_r[0]));
                }else{
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
        if(!isset($closure) or is_string($closure) or !is_callable($closure)){
        	return false;
        }

        $param      = isset($param) ? $param : [];
        $call_value = []; // 闭包参数值
        $reflect    = new ReflectionFunction($closure);

        foreach($reflect->getParameters() as $_param){

            $name = $_param->getName();

            if(isset($param[$name]) and ''!==$param[$name]){
                $call_value[] = $param[$name];
            }else{
                if($_param->isOptional()){
                    $call_value[] = $_param->getDefaultValue();
                }else{
                    $call_value[] = null;
                }
            }

        }

        $ret = call_user_func_array($closure, $call_value);
        return (false === $ret) ? false : true;
    }

    /**
     * set
     *
     * @param string $route
     * @param string $rule
     * @param array $wildcard
     * @return boolean
     */
    private function set($route = '', $rule = null, $wildcard = null)
    {
        if(!isset($route)){
        	return false;
        }

        $this->_routes[$this->_domain][$route] = ['rule'=>$rule, 'wildcard'=>$wildcard];
        return true;
    }

    /**
     * 追加全局约束通配符
     *
     * @return bool
     */
    private function addWildcard($wildcard = [])
    {
        if(empty($wildcard) or !is_array($wildcard)){
        	return false;
        }

    	$this->_wildcard = array_merge($this->_wildcard, $wildcard);
    	return true;
    }

    /**
     * 获取全局约束通配符
     *
     * @return array
     */
    private function getWildcard()
    {
        $wildcard = ['/'=>'\/'];
        foreach($this->_wildcard as $key=>$value){
            $wildcard['{' . $key . '}'] = '(' . $value . ')';
        }
        return $wildcard;
    }

    /**
     * 取路由规则
     *
     * @return array
     */
    private function getRule()
    {
        if(empty($this->_routes)){
            return [];
        }

        $host = $_SERVER['HTTP_HOST'];

        // 是否命中子域名组
        foreach($this->_routes as $domain => $rule){
            if('*' == $domain){
                continue 1;
            }
            $domain = strtr($domain, $this->getWildcard());
            if(preg_match('/^' . $domain . '$/i', $host)){
                return $rule;
            }
        }

        if(isset($this->_routes['*'])){
            return $this->_routes['*'];
        }
        return [];
    }

    /**
     * 将通配符转为正则
     *
     * @param string $rule
     * @param array $wildcard
     * @return string
     */
    private function getPattern($rule = '', $wildcard = [])
    {
        if(!isset($rule) or ''===$rule){
        	return '';
        }

        $_wildcard = $this->getWildcard();

        if(!empty($wildcard) and is_array($wildcard)){
            foreach($wildcard as $key=>$value){
                $_wildcard['{' . $key . '}'] = '(' . $value . ')';
            }
        }

        return strtr(trim($rule, '/'), $_wildcard);
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
		if(!empty($r)){
			foreach($r as $_r){
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
    private function makeControllerByRule($rule = null)
    {
        $r = explode('@', trim($rule, '\\'));
		$action = isset($r[1]) ? $r[1] : '';

        return ['controller'=>trim($r[0], '\\'), 'action'=>$action];
    }

    /**
     * 设置GET
     *
     * @param string $url
     * @return bool
     */
    private function setParam($param = '')
    {
        if(empty($param)){
            return true;
        }

        $get = explode('&', $param);
        foreach($get as $_g){
            $_g = explode('=', $_g);
            $_GET[$_g[0]] = isset($_g[1]) ? $_g[1] : '';
        }
        return true;
    }

    /**
     * getRouteByCli
     *
     * @return string
     */
    private function getRouteByCli()
    {
        return isset($GLOBALS['argv'][1]) ? trim($GLOBALS['argv'][1], '/') : '';
    }

    /**
     * getRouteByGet
     *
     * @return string
     */
    private function getRouteByGet()
    {
        $name = isset($GLOBALS['_G']['route_get']) ? $GLOBALS['_G']['route_get'] : 'r';
        return isset($_GET[$name]) ? trim($_GET[$name], '/') : '';
    }

    /**
     * getRouteByPathInfo
     *
     * @return string
     */
    private function getRouteByPathInfo()
    {
        return isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';
    }

    /**
     * getRouteByRequestUri
     *
     * @return string
     */
    private function getRouteByRequestUri()
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        $script_name = $_SERVER['SCRIPT_NAME'];

        if(substr($request_uri, 0, strlen($script_name)) == $script_name){
            $route = substr($request_uri, strlen($script_name));
        }else{
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
    public function getRoute()
    {
        if(defined('IS_CLI') and IS_CLI){
            return $this->getRouteByCli();
        }

        $url_model = isset($GLOBALS['_G']['url_model']) ? $GLOBALS['_G']['url_model'] : 1;
        if(2 == $url_model){
            $_route = $this->getRouteByPathInfo();
        }elseif(3 == $url_model){
            $_route = $this->getRouteByGet();
        }elseif(4 == $url_model){
            $_route = isset($GLOBALS['_G']['route_func']) ? call_user_func($GLOBALS['_G']['route_func']) : get_route();
        }else{
            $_route = $this->getRouteByRequestUri();
        }
        return $_route;
    }

}
