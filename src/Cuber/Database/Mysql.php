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
            $ret = $this->getMaster()->exec($sql);
            $_e  = microtime(true);

            if ($this->debug) {
                $_log  = 'exec()' . "\n";
                $_log .= 'time: ' . ($_e - $_s) . "\n";
                $_log .= 'sql: ' . print_r($sql, true) . "\n";
                s($_log);
            }

            return $ret;

        } catch (PDOException $e) {

            if ($this->inTransaction()) {
                throw new Exception('exec() ' . $sql . $e->getMessage());
            }

            (new Exception())->log(Exception::ERROR_TYPE_MYSQL, $e);

            return false;
        }
    }

    /**
     * 预处理sql
     *
     * @param string $sql
     * @return $statement
     */
    private function prepare($sql = null)
    {
        $pdo = (!$this->use_master and $this->isReadQuery($sql)) ? $this->getSlave() : $this->getMaster();
        $statement = $pdo->prepare($sql);
        return $statement;
    }

    /**
     * bindParams
     *
     * @param res $statement
     * @param array $param
     * @return bool
     */
    private function bindParams($statement = null, $param = null)
    {
        if (empty($statement)) {
            return false;
        }

        if (!empty($param) and is_array($param)) {
            foreach ($param as $key=>$value) {
                is_int($key) and $key++;
                $statement->bindValue($key, $value, $this->getType($value));
            }
        }

        return true;
    }

    /**
     * 执行预处理sql
     *
     * @param res $statement
     * @param array $param
     *
     * @return bool
     */
    private function execute($statement = null, $param = null)
    {
        if (empty($statement)) {
            return false;
        }

        if (isset($param)) {
            $ret = $statement->execute($param);
        } else {
            $ret = $statement->execute();
        }

        return $ret;
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

            $statement = $this->prepare($sql);
            if(false === $statement){
                return false;
            }

            $this->bindParams($statement, $param);
            $ret = $this->execute($statement);

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

            if ($this->inTransaction()) {
                throw new Exception('query() ' . $sql . $e->getMessage());
            }

            (new Exception())->log(Exception::ERROR_TYPE_MYSQL, $e);
        }
    }

    /**
     * rowCount
     *
     * @param res $statement
     * @return int|false
     */
    public function rowCount($statement = null)
    {
        if (empty($statement)) {
            return false;
        }

        return $statement->rowCount();
    }

    /**
     * fetch
     *
     * @param res $statement
     * @return array|false
     */
    public function fetch($statement = null)
    {
        if (empty($statement)) {
            return false;
        }

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 查询一行记录
     *
     * @return array
     */
    public function line($sql = null, $param = null)
    {
        $statement = $this->query($sql, $param);

        if (false === $statement) {
            return false;
        } else {
            return $statement->fetch(PDO::FETCH_ASSOC);
        }
    }

    /**
     * 查询一个字段
     *
     * @return str
     */
    public function val($sql = null, $param = null)
    {
        $statement = $this->query($sql, $param);

        if (false === $statement) {
            return false;
        } else {
            return $statement->fetchColumn();
        }
    }

    /**
     * 查询多行记录
     *
     * @return array
     */
    public function get($sql = null, $param = null)
    {
        $statement = $this->query($sql, $param);

        if (false === $statement) {
            return false;
        } else {
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * 取得上一步insert操作产生的id
     *
     * @return bigint
     */
    public function lastId()
    {
        return $this->getMaster()->lastInsertId();
    }

    /**
     * 开始一个事务
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->getMaster()->beginTransaction();
    }

    /**
     * 提交事务
     *
     * @return bool
     */
    public function commit()
    {
        return $this->getMaster()->commit();
    }

    /**
     * 回滚事务
     *
     * @return bool
     */
    public function rollBack()
    {
        return $this->getMaster()->rollBack();
    }

    /**
     * 判断是否在一个事务中
     *
     * @return bool
     */
    public function inTransaction()
    {
        return $this->getMaster()->inTransaction();
    }

    /**
     * 执行一组事务
     *
     * @param func $closure
     *
     * @return bool
     */
    public function transaction($closure = null)
    {
        if (!isset($closure) or is_string($closure) or !is_callable($closure)) {
            return false;
        }

        try {
            $this->beginTransaction();
            $closure();
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            $e->log(Exception::ERROR_TYPE_MYSQL);
            return false;
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
     * 获取当前实例的从库连接
     *
     * @return resource
     */
    public function getSlave()
    {
        return $this->conn('slave');
    }

    /**
     * 获取当前实例的主库连接
     *
     * @return resource
     */
    public function getMaster()
    {
        return $this->conn('master');
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
     * conn
     *
     * @param string $mode
     * @return PDO
     */
    private function conn($mode = 'master')
    {
        $conf = $this->config;

        $conn_key = $this->connect . '.' . $mode;
        if (isset($this->conn[$conn_key])) {
            return $this->conn[$conn_key];
        }

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
