<?php

/**
 * Route
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class Route
{

    private static $_instance = null;

    private $_route = null;

    private $_domain = '*';

    private $_pattern = [];

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 通配符
     *
     * @param array $pattern
     * @return bool
     */
    public static function pattern($pattern = [])
    {
        return self::getInstance()->addPattern($pattern);
    }

    /**
     * 路由
     *
     * @return bool
     */
    public static function get($route = '', $rule = null, $pattern = null)
    {
        return self::getInstance()->set($route, $rule, $pattern);
    }

    /**
     * 配置一组路由
     *
     * @return bool
     */
    public static function group($group = [], $rule = null)
    {
        if (!isset($group['domain'])) {
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
     * set
     *
     * @param string $route
     * @param string $rule
     * @param array $pattern
     * @return boolean
     */
    private function set($route = '', $rule = null, $pattern = null)
    {
        if (!isset($route)) {
            return false;
        }

        $this->_route[$this->_domain][$route] = ['rule'=>$rule, 'pattern'=>$pattern];
        return true;
    }

    /**
     * 获取路由规则
     *
     * @return array
     */
    public function getRoute()
    {
        return $this->_route;
    }

    /**
     * 追加全局约束通配符
     *
     * @return bool
     */
    public function addPattern($pattern = [])
    {
        if (empty($pattern) or !is_array($pattern)) {
            return false;
        }

        $this->_pattern = array_merge($this->_pattern, $pattern);
        return true;
    }

    /**
     * 获取全局约束通配符
     *
     * @return array
     */
    public function getPattern()
    {
        $pattern = ['/'=>'\/'];

        foreach ($this->_pattern as $key=>$value) {
            $pattern['{' . $key . '}'] = '(' . $value . ')';
        }

        return $pattern;
    }

}
