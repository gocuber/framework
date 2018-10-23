<?php

/**
 * Controller
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

class Controller
{

    /**
     * display
     *
     * @param string $tpl
     * @param array $data
     *
     * @return void
     */
    protected function display($tpl = '', $data = [])
    {
        if (!isset($tpl) or '' === $tpl) {
            $tpl = strtr(strtolower(\app()->get('controller') . '/' . \app()->get('action')), ['\\'=>'/']);
        }

        View::display($tpl, $data);
    }

    protected function _get($key = null, $default = null)
    {
        if (isset($key)) {
            return \array_get($_GET, $key, $default);
        } else {
            return $_GET;
        }
    }

    protected function _post($key = null, $default = null)
    {
        if (isset($key)) {
            return \array_get($_POST, $key, $default);
        } else {
            return $_POST;
        }
    }

    protected function _request($key = null, $default = null)
    {
        if (isset($key)) {
            return \array_get($_REQUEST, $key, $default);
        } else {
            return $_REQUEST;
        }
    }

    protected function _argv($key = null, $default = null)
    {
        if (isset($key)) {
            return \array_get(\app()->get('argv'), $key, $default);
        } else {
            return \app()->get('argv');
        }
    }

}
