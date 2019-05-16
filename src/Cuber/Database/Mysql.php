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

    /**
     * setConfig
     *
     * @param array $config
     * @return $this
     */
    public function setConfig($config = [])
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
     * 执行一条SQL返回影响行数
     *
     * @return int
     */
    public function exec($sql = null)
    {
        if ($this->isReadQuery($sql)) {
            return $this->query($sql);
        }

        try {

            $_s  = microtime(true);
            $ret = $this->pdo('master')->exec($sql);
            $_e  = microtime(true);

            if ($this->debug) {
                $_log  = 'exec()' . "\n";
                $_log .= 'time: ' . ($_e - $_s) . "\n";
                $_log .= 'sql: ' . print_r($sql, true) . "\n";
                s($_log);
            }

            return $ret;

        } catch (PDOException $e) {

            if ($this->pdo('master')->inTransaction()) {
                throw new Exception('exec() ' . $sql . $e->getMessage());
            }

            (new Exception())->log(Exception::ERROR_TYPE_MYSQL, $e);

            return false;
        }
    }

    /**
     * 执行sql语句
     *
     * @param string $sql
     *
     * @return res|false
     */
    public function query($sql = null, $param = null, $reconn = self::RECONN)
    {
        try {

            $_s = microtime(true);

            $pdo = (!$this->use_master and $this->isReadQuery($sql)) ? $this->pdo('slave') : $this->pdo('master');
            $statement = $pdo->prepare($sql);
            if(false === $statement){
                return false;
            }

            if (!empty($param) and is_array($param)) {
                foreach ($param as $key=>$value) {
                    is_int($key) and $key++;
                    $statement->bindValue($key, $value, $this->getType($value));
                }
            }

            $ret = $statement->execute();

            $_e = microtime(true);

            if ($this->debug) {
                $_log  = 'query()' . "\n";
                $_log .= 'time: ' . ($_e - $_s) . "\n";
                $_log .= 'sql: ' . print_r($sql, true) . "\n";
                $_log .= 'param: ' . print_r($param, true) . "\n";
                s($_log);
                echo '<pre>debugDumpParams: ';
                $statement->debugDumpParams();
                echo '</pre>';
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
        $this->master = null;
        $this->slave  = null;
    }

    /**
     * getDsn
     *
     * @param array $conf
     * @return string
     */
    private function getDsn($conf = [])
    {
        extract($conf);
        if (empty($host) or empty($database)) {
            return false;
        }

        $dsn  = isset($driver) ? $driver : 'mysql';
        $dsn .= ":host={$host};dbname={$database}";
        isset($port)    and ''!==$port    and $dsn .= ";port={$port}";
        isset($charset) and ''!==$charset and $dsn .= ";charset={$charset}";
        return $dsn;
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
            $_log  = 'conn()' . "\n";
            $_log .= 'config: ' . print_r($conf, true) . "\n";
            s($_log);
        }

        $dsn = $this->getDsn($conf);
        extract($conf);

        try {

            // PDO::ATTR_PERSISTENT => true,
            $this->conn[$conn_key] = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"]);
            return $this->conn[$conn_key];

        } catch (PDOException $e) {

            (new Exception())->log(Exception::ERROR_TYPE_MYSQL, $e, true);

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
     * @param string $data
     * @return int
     */
    private function getType($data = null)
    {
        static $type_map = [
            'boolean'  => PDO::PARAM_BOOL,
            'integer'  => PDO::PARAM_INT,
            'string'   => PDO::PARAM_STR,
            'resource' => PDO::PARAM_LOB,
            'NULL'     => PDO::PARAM_NULL,
        ];

        $type = gettype($data);
        return isset($type_map[$type]) ? $type_map[$type] : PDO::PARAM_STR;
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
