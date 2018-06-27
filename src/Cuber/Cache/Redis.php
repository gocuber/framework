<?php

/**
 * Redis
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

class Redis
{

    private static $_instance = null;

    private $_config = null;

    private $_conn   = null;

    private function __construct($config = null)
    {
    	if(isset($config)){
    		$this->setConfig($config);
    	}
    }

    /**
     * connect
     *
     * @return Cache_Redis
     */
    public static function connect($conf = null, $mode = 'master')
    {
        empty($conf) and $conf = 'default';

        if(!is_array($conf)){
            $conf = (!empty($GLOBALS['_G']['redis'][$conf]) and is_array($GLOBALS['_G']['redis'][$conf])) ? $GLOBALS['_G']['redis'][$conf] : array();
        }

        $key = md5($conf['host'] . '_' . $conf['port']);
        if('slave' == $mode){
        	$key .= '_slave';
        }
        if(!isset(self::$_instance[$key])){
            if('slave' == $mode and !empty($conf['slave']) and is_array($conf['slave'])){
                $skey = mt_rand(0, count($conf['slave']) - 1);
                $conf = array_merge($conf, $conf['slave'][$skey]);
            }
            self::$_instance[$key] = new self($conf);
        }
        return self::$_instance[$key];
    }

    /**
     * master
     *
     * @return Cache_Redis
     */
    public static function master($conf = null)
    {
        return self::connect($conf, 'master');
    }

    /**
     * slave
     *
     * @return Cache_Redis
     */
    public static function slave($conf = null)
    {
        return self::connect($conf, 'slave');
    }

    public function __call($name = null, $arguments = null)
    {
        try {
            $conn = $this->conn();

            if(is_callable(array($conn, $name))){
                return call_user_func_array(array($conn, $name), $arguments);
            }else{
                throw new CubeException($name . 'error');
            }
        } catch (CubeException $e) {
            $e->log(CubeException::ERROR_TYPE_REDIS);
        }
    }

    /**
     * conn
     *
     * @return Redis
     */
    private function conn()
    {
        if(!isset($this->_conn) or '+PONG'!==$this->_conn->ping()){
            $config = $this->getConfig();

            $key  = md5($config['host'] . '_' . $config['port']);
            $conn = new Redis();
            $conn->pconnect($config['host'], $config['port'], 2, $key);
            if(isset($config['auth'])){
                $conn->auth($config['auth']);
            }

            $this->_conn = $conn;
        }
        return $this->_conn;
    }

    /**
     * setConfig
     *
     * @param array $config
     * @return bool
     */
    private function setConfig($config = null)
    {
        try {
            if(empty($config) or !is_array($config)){
                throw new CubeException("redis config error");
            }
        } catch (CubeException $e) {
            $e->log(CubeException::ERROR_TYPE_REDIS);
        }

        if(empty($config)){
            return false;
        }

        $this->_config = $config;
        return true;
    }

    /**
     * getConfig
     *
     * @return array
     */
    private function getConfig()
    {
        return $this->_config;
    }

}
