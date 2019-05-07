<?php

/**
 * Redis
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Redis;

class Redis
{

    /**
     * 配置
     *
     * @var array
     */
    private $config;

    /**
     * 当前连接
     *
     * @var string
     */
    private $connect = 'default';

    /**
     * mode
     *
     * @var string
     */
    private $mode = 'master';

    /**
     * 连接
     *
     * @var array
     */
    private $conn;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * connect
     *
     * @return $this
     */
    public function connect($key = 'default', $mode = 'master')
    {
        $this->connect = $key;
        $this->mode = $mode;

        return $this;
    }

    /**
     * master
     *
     * @return Redis
     */
    public function master($key = 'default')
    {
        return $this->connect($key, 'master');
    }

    /**
     * slave
     *
     * @return Redis
     */
    public function slave($key = 'default')
    {
        return $this->connect($key, 'slave');
    }

    /**
     * conn
     *
     * @return \Redis
     */
    private function conn()
    {
        $conf = array_get($this->config, $this->connect);

        $conn_key = $this->connect . '.' . $this->mode;
        if (isset($this->conn[$conn_key]) and '+PONG' === $this->conn[$conn_key]->ping()) {
            return $this->conn[$conn_key];
        }

        if ('slave' == $this->mode and !empty($conf['slave']) and is_array($conf['slave'])) {
            $skey = mt_rand(0, count($conf['slave']) - 1);
            $conf = array_merge($conf, $conf['slave'][$skey]);
        }

        $this->conn[$conn_key] = new \Redis();
        $this->conn[$conn_key]->pconnect($conf['host'], $conf['port'], 2, md5($conf['host'] . '_' . $conf['port']));
        isset($conf['auth']) and '' !== $conf['auth'] and $this->conn[$conn_key]->auth($conf['auth']);
        isset($conf['database']) and '' !== $conf['database'] and $this->conn[$conn_key]->select($conf['database']);

        return $this->conn[$conn_key];
    }

    public function __call($method, $args)
    {
        return $this->conn()->$method(...$args);
    }

}
