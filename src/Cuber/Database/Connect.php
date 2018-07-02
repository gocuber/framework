<?php

/**
 * Connect
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Database;

use PDO;
use PDOException;
use Cuber\Support\Exception;

class Connect
{

    const RECONN = 3;

    private static $_debug = null;

    private static $_instance = null;

    private $_conf = null;

    private $_config = null;

    private $_use_master = false;

    private $_master = null;

    private $_slave = null;

    private function __construct($config = null)
    {
    	if (isset($config)) {
    		$this->setConfig($config);
    	}
    }

    public static function getInstance($config = [])
    {
        $key = md5(serialize($config));
        if (!isset(self::$_instance[$key])) {
            self::$_instance[$key] = new self($config);
        }

        return self::$_instance[$key];
    }

    /**
     * 执行一条SQL返回影响行数
     *
     * @return int
     */
    public function exec($sql = null)
    {
        if($this->isReadQuery($sql)){
            return $this->query($sql);
        }

        try {

            $_s  = microtime(true);
            $ret = $this->getMaster()->exec($sql);
            $_e  = microtime(true);

            if(self::$_debug){
                $_log  = '<pre>' . "\n" . 'exec()' . "\n";
                $_log .= 'config : ' . print_r($this->getConfig('master'), true) . "\n";
                $_log .= 'time : ' . ($_e - $_s) ."\n";
                $_log .= 'sql : ' . print_r($sql, true) . "\n";
                $_log .= '</pre>';
                echo $_log;
            }

            return $ret;

        } catch (PDOException $e) {

            if($this->inTransaction()){
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
        $pdo = (!$this->_use_master and $this->isReadQuery($sql)) ? $this->getSlave() : $this->getMaster();
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
        if(empty($statement)){
        	return false;
        }

        if(!empty($param) and is_array($param)){
            foreach($param as $key=>$value){
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
        if(empty($statement)){
        	return false;
        }

        if(isset($param)){
            $ret = $statement->execute($param);
        }else{
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

            if(self::$_debug){
                $_log  = '<pre>' . "\n" . 'query()' . "\n";
                $_log .= 'config : ' . print_r($this->getConfig($this->isReadQuery($sql) ? 'slave' : 'master'), true) . "\n";
                $_log .= 'time : ' . ($_e - $_s) ."\n";
                $_log .= 'sql : ' . print_r($sql, true) . "\n";
                $_log .= 'param : ' . print_r($param, true) . "\n";
                $_log .= '</pre>';
                echo $_log;
                echo '<pre>debugDumpParams : ';$statement->debugDumpParams();echo '</pre>';
            }

            return (false === $ret) ? false : $statement;

        } catch (PDOException $e) {

            if($reconn > 0 and 2006 == $e->errorInfo[1]){
                if(self::$_debug){
                    echo '<pre>reconn : ' . $reconn . '</pre>';
                }
                $reconn--;
                $this->close();
                return $this->query($sql, $param, $reconn);
            }

            if($this->inTransaction()){
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
        if(empty($statement)){
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
        if(empty($statement)){
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

        if(false === $statement){
            return false;
        }else{
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

        if(false === $statement){
            return false;
        }else{
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

        if(false === $statement){
            return false;
        }else{
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
        if(!isset($closure) or is_string($closure) or !is_callable($closure)){
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
        $this->_master = null;
        $this->_slave  = null;
    }

    /**
     * 切换到读写主库
     *
     * @param bool $is
     * @return bool
     */
    public function useMaster($is = true)
    {
        $this->_use_master = $is;
        return true;
    }

    /**
     * 获取当前实例的从库连接
     *
     * @return resource
     */
    public function getSlave()
    {
        if(empty($this->_config['slave'])){
        	return $this->getMaster();
        }
        if(!isset($this->_slave)){
    	   $this->_slave = $this->conn($this->getConfig('slave'));
        }
    	return $this->_slave;
    }

    /**
     * 获取当前实例的主库连接
     *
     * @return resource
     */
    public function getMaster()
    {
        if(!isset($this->_master)){
    	   $this->_master = $this->conn($this->getConfig('master'));
        }
    	return $this->_master;
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
        if(empty($host) or empty($database)){
        	return false;
        }

        $dsn   = isset($driver) ? $driver : 'mysql';
        $dsn  .= ":host={$host};dbname={$database}";
        (isset($port) and ''!==$port)       and $dsn .= ";port={$port}";
        (isset($charset) and ''!==$charset) and $dsn .= ";charset={$charset}";
        return $dsn;
    }

    /**
     * conn
     *
     * @param array $conf
     * @return PDO
     */
    private function conn($conf = [])
    {
        $dsn = $this->getDsn($conf);
        extract($conf);

        try {
            // PDO::ATTR_PERSISTENT => true,
            return new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"]);
        } catch (PDOException $e) {
            (new Exception())->log(Exception::ERROR_TYPE_MYSQL, $e, true);
        }
    }

    /**
     * setConfig
     *
     * @param array $config
     * @return $this
     */
    public function setConfig($config = [])
    {
        try {
            if(!isset($config) or !is_array($config)){
                throw new Exception("database config error");
            }
        } catch (Exception $e) {
            $e->log(Exception::ERROR_TYPE_MYSQL);
        }

        $this->_config = $config;
        return $this;
    }

    /**
     * getConfig
     *
     * @return array
     */
    public function getConfig($mode = null)
    {
        $conf = $this->_config;
        if (empty($mode) or !in_array($mode, ['master', 'slave'])) {
        	return $conf;
        }

        if (!isset($this->_conf[$mode])) {
            if('slave' == $mode and !empty($conf['slave']) and is_array($conf['slave'])){
                if(isset($conf['slave'][0]) and is_array($conf['slave'][0])){
                    $skey = mt_rand(0, count($conf['slave']) - 1);
                    $conf = array_merge($conf, $conf['slave'][$skey]);
                }else{
                    $conf = array_merge($conf, $conf['slave']);
                }
            }
            unset($conf['slave']);
            $this->_conf[$mode] = $conf;
        }

        return $this->_conf[$mode];
    }

    /**
     * debug
     *
     * @param bool $debug
     * @return void
     */
    public function debug($debug = true)
    {
    	self::$_debug = $debug;
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
        $pattern = '/^\s*(SELECT|SHOW|DESCRIBE)\b/i';
        return preg_match($pattern, $sql) > 0;
    }

}
