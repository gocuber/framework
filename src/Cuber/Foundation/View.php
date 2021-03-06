<?php

/**
 * View
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class View
{

    private $public_data;  // 公有数据 全局数据

    private $private_data; // 私有数据 临时数据

    /**
     * assign
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public function assign($key = null, $value = null)
    {
        if (!isset($key)) {
            return null;
        }

        if (is_scalar($key)) {
            $this->public_data[$key] = $value;
        } elseif (!empty($key) and is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->public_data[$k] = $v;
            }
        }

        return null;
    }

    /**
     * display
     *
     * @param string $_tpl
     * @param array $_data
     *
     * @return void
     */
    public function display($_tpl = null, $_data = null)
    {
        if (null === $_tpl or '' === $_tpl) {
            $_tpl = strtr(strtolower(app('app.controller') . '/' . app('app.action')), ['\\'=>'/']);
        }

        if (!empty($_data) and is_array($_data)) {
            $this->assign($_data);
        }

        foreach ([$this->public_data, $this->private_data] as $_item) {
            if (!empty($_item) and is_array($_item)) {
                foreach ($_item as $_key => $_value) {
                    $$_key = $_value;
                }
            }
        }

        include config('views', base_path('app/views/')) . $_tpl . '.php';
    }

    /**
     * load
     *
     * @param string $tpl
     * @param array $data
     *
     * @return void
     */
    public function load($tpl = null, $data = null)
    {
        if (!empty($data) and is_array($data)) {
            $this->private_data = $data;
        }

        return $this->display($tpl, null);
    }

    /**
     * 加载组件
     *
     * @param string $_tpl
     * @param array $_data
     *
     * @return void
     */
    public function component($_tpl = null, array $_data = [])
    {
        if (null === $_tpl or '' === $_tpl) {
            return null;
        }

        $_components_data = app('app.components.' . $_tpl);
        if (!empty($_components_data)) {
            foreach ($_components_data as $_key => $_value) {
                $$_key = $_value;
            }
        }
        if (!empty($_data)) {
            foreach ($_data as $_key => $_value) {
                $$_key = $_value;
            }
        }

        include config('views', base_path('app/views/')) . $_tpl . '.php';
    }

    /**
     * 组件赋值
     *
     * @param string $name
     * @param array $data
     *
     * @return void
     */
    public function componentAssign($name = null, array $data = [])
    {
        if (null === $name or empty($data)) {
            return null;
        }

        if (is_array($name)) {
            foreach ($name as $key => $value) {
                app()->bind('app.components.' . $key, $value);
            }
        } else {
            app()->bind('app.components.' . $name, $data);
        }

        return null;
    }

}
