<?php

/**
 * DatabaseManager
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Database;

use Closure;
use PDO;
use PDOStatement;

class DatabaseManager
{

    private $app;

    private $config;

    private $query;

    protected $connect;

    private $use_master = false;

    private $call_transaction = [
        'beginTransaction',
        'commit',
        'rollBack',
        'inTransaction',
    ];

    private $call_driver = [
        'pdo',
        'debug',
    ];

    public function __construct($app, $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->connect();
    }

    public function model(Model $model)
    {
        return $this->connect($model->getConnect())->name($model->getName());
    }

    public function connect($key = null)
    {
        if (null === $key) {
            $this->connect = array_get($this->config, 'default', 'default');
        } else {
            $this->connect = $key;
        }

        return $this;
    }

    /**
     * 切换到读写主库
     *
     * @param bool $is
     * @return this
     */
    public function useMaster($is = true)
    {
        $this->use_master = $is;

        return $this;
    }

    public function getDriver()
    {
        $config = array_get($this->config, 'connects.' . $this->connect, []);
        $driver = array_get($config, 'driver', 'mysql');

        return $this->app->make('db.' . $driver)
            ->setConfig($config)
            ->connect($this->connect)
            ->useMaster($this->use_master);
    }

    /**
     * 查询 总数
     *
     * @return int
     */
    public function count()
    {
        return $this->val("count(*)");
    }

    /**
     * 查询 最大值
     *
     * @param string $field
     * @return int
     */
    public function max($field)
    {
        return $this->val("max($field)");
    }

    /**
     * 查询 最小值
     *
     * @param string $field
     * @return int
     */
    public function min($field)
    {
        return $this->val("min($field)");
    }

    /**
     * 查询 求平均值
     *
     * @param string $field
     * @return float
     */
    public function avg($field)
    {
        return $this->val("avg($field)");
    }

    /**
     * 查询 数据相加 求和
     *
     * @param string $field
     * @return int
     */
    public function sum($field)
    {
        return $this->val("sum($field)");
    }

    /**
     * 查询 返回 hash数组
     *
     * @param string $key
     * @param string $value
     * @return array $array
     */
    public function hash($key = 'id', $value = '*')
    {
        $field = ('*' == $value or $key == $value) ? $value : "{$key},{$value}";
        $res = $this->getQuery()->field($field)->buildSelect();
        $statement = $this->getDriver()->query($res['sql'], $res['param']);

        if (false === $statement) {
            return false;
        } else {
            $hash = [];
            $is = ('*' == $value or count(explode(',', $value)) > 1) ? 1 : 0;
            for (;$v = $statement->fetch(PDO::FETCH_ASSOC);) {
                $hash[$v[$key]] = $is ? $v : $v[$value];
            }
            return $hash;
        }
    }

    /**
     * 查询 返回多条数据
     *
     * @param string $field
     * @return array $array
     */
    public function get($field = null)
    {
        $res = $this->getQuery()->field($field)->buildSelect();
        $statement = $this->getDriver()->query($res['sql'], $res['param']);

        if (false === $statement) {
            return false;
        } else {
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * 查询 返回一条数据
     *
     * @param string $field
     * @return array $array
     */
    public function line($field = null)
    {
        $res = $this->getQuery()->field($field)->buildSelect();
        $statement = $this->getDriver()->query($res['sql'], $res['param']);

        if (false === $statement) {
            return false;
        } else {
            return $statement->fetch(PDO::FETCH_ASSOC);
        }
    }

    /**
     * 查询 返回一个字段
     *
     * @param string $field
     * @return str $val
     */
    public function val($field = null)
    {
        $res = $this->getQuery()->field($field)->buildSelect();
        $statement = $this->getDriver()->query($res['sql'], $res['param']);

        if (false === $statement) {
            return false;
        } else {
            return $statement->fetchColumn();
        }
    }

    /**
     * 执行sql语句
     *
     * @param string $sql
     *
     * @return \PDOStatement|false
     */
    public function query($sql = null, $param = null)
    {
        if (empty($sql)) {
            $res = $this->getQuery()->buildSelect();
            $sql = $res['sql'];
            $param = $res['param'];
        }

        return $this->getDriver()->query($sql, $param);
    }

    /**
     * fetch
     *
     * @param \PDOStatement $statement
     * @return array|false
     */
    public function fetch(PDOStatement $statement = null)
    {
        if (empty($statement)) {
            return false;
        }

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 查询
     *
     * @param str $sql
     * @param array $param
     *
     * @return array|false
     */
    public function select($sql = null, $param = null)
    {
        $statement = $this->getDriver()->query($sql, $param);

        if (false === $statement) {
            return false;
        } else {
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * 插入
     *
     * @param str|array $sql
     * @param array $param
     *
     * @return int|false
     */
    public function insert($sql = [], $param = null)
    {
        if (empty($sql) and !is_array($sql)) {
            return false;
        }

        if (is_array($sql)) {
            $res = $this->getQuery()->insert($sql)->buildInsert();
            $sql = $res['sql'];
            $param = $res['param'];
        }

        $statement = $this->getDriver()->query($sql, $param);
        if (false === $statement) {
            return false;
        } else {
            return $this->pdo()->lastInsertId();
        }
    }

    /**
     * 批量插入数据
     *
     * @param array $data
     *
     * @return int|false
     */
    public function batchInsert(array $data = [])
    {
        if (empty($data)) {
            return false;
        }

        $res = $this->getQuery()->batchInsert($data)->buildBatchInsert();
        $sql = $res['sql'];
        $param = $res['param'];

        $statement = $this->getDriver()->query($sql, $param);
        if (false === $statement) {
            return false;
        } else {
            return $statement->rowCount();
        }
    }

    /**
     * 修改
     *
     * @param str|array $sql
     * @param array $param
     *
     * @return int|false
     */
    public function update($sql = null, $param = null)
    {
        if (empty($sql)) {
            return false;
        }

        if (is_array($sql)) {
            $res = $this->getQuery()->update($sql)->buildUpdate();
            $sql = $res['sql'];
            $param = $res['param'];
        }

        $statement = $this->getDriver()->query($sql, $param);
        if (false === $statement) {
            return false;
        } else {
            return $statement->rowCount();
        }
    }

    /**
     * 删除
     *
     * @param str $sql
     * @param array $param
     *
     * @return int|false
     */
    public function delete($sql = null, $param = null)
    {
        if (empty($sql)) {
            $res = $this->getQuery()->buildDelete();
            $sql = $res['sql'];
            $param = $res['param'];
        }

        $statement = $this->getDriver()->query($sql, $param);
        if (false === $statement) {
            return false;
        } else {
            return $statement->rowCount();
        }
    }

    /**
     * 执行一组事务
     *
     * @param Closure $closure
     *
     * @return bool
     */
    public function transaction(Closure $closure)
    {
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
     * getQuery
     *
     * @return Query
     */
    protected function getQuery()
    {
        if (!isset($this->query)) {
            $this->query = $this->app->make('db.query');
        }

        return $this->query;
    }

    public function __call($name, $args)
    {
        if (in_array($name, $this->call_transaction)) {
            return $this->pdo()->$name(...$args);
        } elseif (in_array($name, $this->call_driver)) {
            return $this->getDriver()->$name(...$args);
        } elseif (is_callable([$this->getQuery(), $name])) {
            $this->getQuery()->$name(...$args);
            return $this;
        }
    }

}
