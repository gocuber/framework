<?php

/**
 * Connect
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Redis;

class Connect
{

    private $conf;

    private $conn;

    public function __construct($conf)
    {
        $this->conf = $conf;
    }

    /**
     * conn
     *
     * @return \Redis
     */
    private function conn()
    {
        $conf = $this->conf;

        if (!isset($this->conn) or '+PONG' !== $this->conn->ping()) {
            $this->conn = new \Redis();
            $this->conn->pconnect($conf['host'], $conf['port'], 2, md5($conf['host'] . '_' . $conf['port']));
            isset($conf['auth']) and '' !== $conf['auth'] and $this->conn->auth($conf['auth']);
            isset($conf['database']) and '' !== $conf['database'] and $this->conn->select($conf['database']);
        }

        return $this->conn;
    }

    public function __call($name, $args)
    {
        return $this->conn()->$name(...$args);
    }

}
