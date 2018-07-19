<?php

/**
 * Controller
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Foundation;

use Cuber\Foundation\View;

class Controller
{

    protected $_route = '';

    protected $_controller = '';

    protected $_action = '';

    protected $_argv = [];

    public function __construct($opt = [])
    {
        foreach (['_route', '_controller', '_action', '_argv'] as $key) {
            if (isset($opt[$key])) {
                $this->$key = $opt[$key];
            }
        }

        // parse_str(implode('&', $GLOBALS['argv']), $_GET);
        defined('IS_CLI') and IS_CLI === true and $this->_argv = get_argv();
    }

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
        if (!isset($tpl) or ''===$tpl) {
            $tpl = strtr(strtolower($this->_controller . '/' . $this->_action), ['\\'=>'/']);
        }

        $data['_route']      = $this->_route;
        $data['_controller'] = $this->_controller;
        $data['_action']     = $this->_action;
        $data['_argv']       = $this->_argv;

        View::display($tpl, $data);
    }

    protected function _get($key = null, $default = null)
    {
        if (isset($key)) {
            return array_get($_GET, $key, $default);
        } else {
            return $_GET;
        }
    }

    protected function _post($key = null, $default = null)
    {
        if (isset($key)) {
            return array_get($_POST, $key, $default);
        } else {
            return $_POST;
        }
    }

    protected function _request($key = null, $default = null)
    {
        if (isset($key)) {
            return array_get($_REQUEST, $key, $default);
        } else {
            return $_REQUEST;
        }
    }

}
