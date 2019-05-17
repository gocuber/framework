<?php

/**
 * Mysql
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Database;

use PDO;
use PDOException;
use Cuber\Support\Exception;

class Mysql
{

    const RECONN = 3;

    private $debug = false;

    private $config = [];

    private $connect = 'default';

    private $use_master = false;

    private $conn;

    private $type_map = [
        'boolean' => PDO::PARAM_BOOL,
        'integer' => PDO::PARAM_INT,
        'string'  => PDO::PARAM_STR,
        'NULL'    => PDO::PARAM_NULL,
    ];

    /**
     * setConfig
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * connect
     *
     * @return $this
     */
    public function connect($key = 'default')
    {
        $this->connect = $key;

        return $this;
    }

    /**
     * 切换到读写主库
     *
     * @param bool $is
     * @return bool
     */
    public function useMaster($is = true)
    {
        $this->use_master = $is;

        return $this;
    }

    /**
     * 执行sql语句
     *
     * @param string $sql
     *
     * @return \PDOStatement|false
     */
    public function query($sql, array $param = [], $reconn = self::RECONN)
    {
        try {

            $_s = microtime(true);
            $pdo = (!$this->use_master and $this->isReadQuery($sql)) ? $this->pdo('slave') : $this->pdo('master');
            $statement = $pdo->prepare($sql);
            foreach ($param as $key => $value) {
                $statement->bindValue(is_int($key) ? $key + 1 : $key, $value, $this->getType($value));
            }
            $ret = $statement->execute();
            $_e = microtime(true);

            if ($this->debug) {
                $_log  = 'time: ' . ($_e - $_s) . "\n";
                $_log .= 'sql: ' . print_r($sql, true) . "\n";
                $_log .= 'param: ' . print_r($param, true) . "\n";
                s($_log);
            }

            return (false === $ret) ? false : $statement;

        } catch (PDOException $e) {

            if ($reconn > 0 and 2006 == $e->errorInfo[1]) {
                if ($this->debug) {
                    s('reconn: ' . $reconn);
                }
                $reconn--;
                $this->close();
                return $this->query($sql, $param, $reconn);
            }

            if (!((!$this->use_master and $this->isReadQuery($sql))) and $this->pdo('master')->inTransaction()) {
                throw new Exception('query() ' . $sql . $e->getMessage());
            }

            (new Exception())->log(Exception::ERROR_TYPE_MYSQL, $e);
        }
    }

    /**
     * 关闭当前实例的主从连接
     */
    public function close()
    {
        $this->conn[$this->connect . 'master'] = null;
        $this->conn[$this->connect . 'slave'] = null;
    }

    /**
     * pdo
     *
     * @param string $mode
     * @return PDO
     */
    public function pdo($mode = 'master')
    {
        $conn_key = $this->connect . '.' . $mode;
        if (isset($this->conn[$conn_key])) {
            return $this->conn[$conn_key];
        }

        $conf = $this->config;

        if ('slave' == $mode and !empty($conf['slave']) and is_array($conf['slave'])) {
            $skey = mt_rand(0, count($conf['slave']) - 1);
            $conf = array_merge($conf, $conf['slave'][$skey]);
        }

        if ($this->debug) {
            s('config: ' . print_r($conf, true));
        }

        try {

            $this->conn[$conn_key] = new PDO(
                $this->getDsn($conf),
                array_get($conf, 'username'),
                array_get($conf, 'password'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "set names '" . array_get($conf, 'charset', 'utf8') . "'"
                ]
            );
            return $this->conn[$conn_key];

        } catch (PDOException $e) {

            (new Exception())->log(Exception::ERROR_TYPE_MYSQL, $e, true);

        }
    }

    /**
     * getDsn
     *
     * @param  array  $config
     * @return string
     */
    private function getDsn(array $config)
    {
        extract($config);

        if (!empty($unix_socket)) {
            return "mysql:unix_socket={$unix_socket};dbname={$database}";
        } else {
            return isset($port) ? "mysql:host={$host};port={$port};dbname={$database}" : "mysql:host={$host};dbname={$database}";
        }
    }

    /**
     * debug
     *
     * @param bool $debug
     * @return void
     */
    public function debug($debug = true)
    {
        $this->debug = $debug;
    }

    /**
     * getType
     *
     * @param mixed $value
     * @return int
     */
    private function getType($value)
    {
        $type = gettype($value);
        return isset($this->type_map[$type]) ? $this->type_map[$type] : PDO::PARAM_STR;
    }

    /**
     * isReadQuery
     *
     * @param string $sql
     * @return bool
     */
    private function isReadQuery($sql = null)
    {
        return preg_match('/^\s*(select|show|desc|describe)\b/i', $sql) > 0;
    }

}
