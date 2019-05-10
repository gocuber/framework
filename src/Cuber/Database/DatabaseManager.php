<?php

/**
 * DatabaseManager
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Database;

class DatabaseManager
{

    private $app;

    private $config = [];

    private $query = null;

    protected $connect = 'default';

    private $use_master = false;

    public function __construct($app, $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function model(Model $model)
    {
        $this->connect = $model->getConnect();
        $this->name    = $model->getName();
        $this->fields  = $model->getFields();

        return $this;
    }

    public function connect($key = 'default')
    {
        $this->connect = $key;

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
        $driver = array_get($this->config, $this->connect . '.driver', 'mysql');

        return $this->app->make('db.' . $driver)
            ->setConfig(array_get($this->config, $this->connect))
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
    public function max($field = null)
    {
        if (!isset($field)) {
            return false;
        }

        return $this->val("max($field)");
    }

    /**
     * 查询 最小值
     *
     * @param string $field
     * @return int
     */
    public function min($field = null)
    {
        if (!isset($field)) {
            return false;
        }

        return $this->val("min($field)");
    }

    /**
     * 查询 求平均值
     *
     * @param string $field
     * @return float
     */
    public function avg($field = null)
    {
        if (!isset($field)) {
            return false;
        }

        return $this->val("avg($field)");
    }

    /**
     * 查询 数据相加 求和
     *
     * @param string $field
     * @return int
     */
    public function sum($field = null)
    {
        if (!isset($field)) {
            return false;
        }

        return $this->val("sum($field)");
    }

    /**
     * 查询 返回 hash数组
     *
     * @param string $key
     * @param string $value
     * @return array $array
     */
    public function hash($key = '', $value = '*')
    {
        $field = ('*' == $value or $key == $value) ? $value : "{$key},{$value}";
        $this->getQuery()->field($field);

        $sql  = $this->getQuery()->getSql();

        $is   = ('*' == $value or count(explode(',', $value)) > 1) ? 1 : 0;
        $hash = [];

        $query = $this->getDriver()->query($sql['sql'], $sql['param']);
        for(;$v = $this->getDriver()->fetch($query);){
            $hash[$v[$key]] = $is ? $v : $v[$value];
        }

        return $hash;
    }

    /**
     * 查询 返回多条数据
     *
     * @param string $field
     * @return array $array
     */
    public function get($field = null)
    {
        $this->getQuery()->field($field);
        $sql = $this->getQuery()->getSql();
        return $this->getDriver()->get($sql['sql'], $sql['param']);
    }

    /**
     * 查询 返回一条数据
     *
     * @param string $field
     * @return array $array
     */
    public function line($field = null)
    {
        $this->getQuery()->field($field);
        $sql = $this->getQuery()->getSql();
        return $this->getDriver()->line($sql['sql'], $sql['param']);
    }

    /**
     * 查询 返回一个字段
     *
     * @param string $field
     * @return str $val
     */
    public function val($field = null)
    {
        $this->getQuery()->field($field);
        $sql = $this->getQuery()->getSql();
        return $this->getDriver()->val($sql['sql'], $sql['param']);
    }

    /**
     * where条件
     *
     * @param array $where
     *
     * @return $this
     */
    public function where($where = null)
    {
        $this->getQuery()->where($where);

        return $this;
    }

    /**
     * 追加 andWhere 条件
     *
     * @see where()
     * @return $this
     */
    public function andWhere($where = null)
    {
        $this->getQuery()->andWhere($where);

        return $this;
    }

    /**
     * 追加 orWhere 条件
     *
     * @see where()
     * @return $this
     */
    public function orWhere($where = null)
    {
        $this->getQuery()->orWhere($where);

        return $this;
    }

    /**
     * orderBy
     *
     * @param string $orderby
     * @return $this
     */
    public function orderBy($orderby = null)
    {
        $this->getQuery()->orderBy($orderby);

        return $this;
    }

    /**
     * groupBy
     *
     * @param string $groupby
     * @return $this
     */
    public function groupBy($groupby = null)
    {
        $this->getQuery()->groupBy($groupby);

        return $this;
    }

    /**
     * having
     *
     * @param string $having
     * @return $this
     */
    public function having($having = null)
    {
        $this->getQuery()->having($having);

        return $this;
    }

    /**
     * offset
     *
     * @param number $offset
     * @return $this
     */
    public function offset($offset = 0)
    {
        $this->getQuery()->offset($offset);

        return $this;
    }

    /**
     * limit
     *
     * @param number $limit
     * @return $this
     */
    public function limit($limit = 0)
    {
        $this->getQuery()->limit($limit);

        return $this;
    }

    /**
     * page
     *
     * @param number $currpage
     * @param number $pagesize
     *
     * @return $this
     */
    public function page($currpage = 1, $pagesize = 1)
    {
        $this->getQuery()->page($currpage, $pagesize);

        return $this;
    }

    /**
     * innerJoin
     *
     * @param str $table
     * @param str $on
     *
     * @return $this
     */
    public function innerJoin($table = null, $on = null)
    {
        $this->getQuery()->join('inner join', $table, $on);

        return $this;
    }

    /**
     * leftJoin
     *
     * @param str $table
     * @param str $on
     *
     * @return $this
     */
    public function leftJoin($table = null, $on = null)
    {
        $this->getQuery()->join('left join', $table, $on);

        return $this;
    }

    /**
     * rightJoin
     *
     * @param str $table
     * @param str $on
     *
     * @return $this
     */
    public function rightJoin($table = null, $on = null)
    {
        $this->getQuery()->join('right join', $table, $on);

        return $this;
    }

    /**
     * 执行一条SQL返回影响行数
     *
     * @return int
     */
    public function exec($sql = null)
    {
        return $this->getDriver()->exec($sql);
    }

    /**
     * 执行sql语句
     *
     * @param string $sql
     *
     * @return res|false
     */
    public function query($sql = null, $param = null)
    {
        if (empty($sql)) {
            $_sql  = $this->getQuery()->getSql();
            $sql   = $_sql['sql'];
            $param = $_sql['param'];
        }

        return $this->getDriver()->query($sql, $param);
    }

    /**
     * rowCount
     *
     * @param res $statement
     *
     * @return array|false
     */
    public function rowCount($statement = null)
    {
        return $this->getDriver()->rowCount($statement);
    }

    /**
     * fetch
     *
     * @param res $statement
     *
     * @return array|false
     */
    public function fetch($statement = null)
    {
        return $this->getDriver()->fetch($statement);
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
        return $this->getDriver()->get($sql, $param);
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

            $data  = $this->prepareData($sql);
            $param = null;
            $sql   = "insert into `" . $this->getName() . "`";

            $cols = $values = '';
            if (!empty($data) and is_array($data)) {
                $index = 1;
                foreach ($data as $key => $value) {
                    $cols   .= "`" . trim($key) . "`,";
                    $values .= ":pi$index,";
                    $param[":pi$index"] = trim($value);
                    $index++;
                }
                $cols   = rtrim($cols, ',');
                $values = rtrim($values, ',');
                $sql   .= " ({$cols}) values ({$values})";
            } else {
                $sql .= " () values ()";
            }

        }

        $query = $this->getDriver()->query($sql, $param);
        if (false === $query) {
            return false;
        } else {
            return $this->getDriver()->lastId();
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

            $data  = $this->prepareData($sql);
            $param = null;
            if (empty($data) or !is_array($data)) {
                return false;
            }

            $field = '';
            $index = 1;
            foreach ($data as $key => $value) {
                $field .= "`" . trim($key) . "`=:pu$index,";
                $param[":pu$index"] = trim($value);
                $index++;
            }
            $field = rtrim($field, ',');
            if (empty($field)) {
                return false;
            }

            $sql  = "update `" . $this->getName() . "` set $field";
            $_sql = $this->getQuery()->getSql();

            !empty($_sql['where'])   and $sql .= " where " . $_sql['where'];
            !empty($_sql['orderby']) and $sql .= " order by " . $_sql['orderby'];
            !empty($_sql['limit'])   and $sql .= " limit " . $_sql['limit'];
            !empty($_sql['param'])   and $param = array_merge($param, $_sql['param']);
        }

        $query = $this->getDriver()->query($sql, $param);
        if (false === $query) {
            return false;
        } else {
            return $this->getDriver()->rowCount($query);
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
            $sql  = "delete from `" . $this->getName() . "`";
            $_sql = $this->getQuery()->getSql();

            !empty($_sql['where'])   and $sql .= " where " . $_sql['where'];
            !empty($_sql['orderby']) and $sql .= " order by " . $_sql['orderby'];
            !empty($_sql['limit'])   and $sql .= " limit " . $_sql['limit'];

            $param = $_sql['param'];
        }

        $query = $this->getDriver()->query($sql, $param);
        if (false === $query) {
            return false;
        } else {
            return $this->getDriver()->rowCount($query);
        }
    }

    /**
     * 批量插入数据
     *
     * @param array $data sql或二维数组
     * @param array $param sql使用
     *
     * @return int|false 成功返回行数 失败返回false
     */
    public function batchInsert($data = null, $param = null)
    {
        if (empty($data)) {
            return false;
        }

        if (is_array($data)) {

            $param = null;
            $sql = "insert into `" . $this->name . "`"; // sql 1

            $field = '';
            foreach($data as $fieldline){
                $fieldline = $this->prepareData($fieldline);
                break 1;
            }
            if(empty($fieldline) or !is_array($fieldline)){
                return false;
            }
            foreach($fieldline as $key => $value){
                $field .= "`" . trim($key) . "`,";
            }
            $field = rtrim($field, ',');
            $sql .= " ($field) values "; // sql 2

            $index = 1;
            $values = '';
            foreach($data as $line){
                $line = $this->prepareData($line);
                if(empty($line) or !is_array($line)){
                    continue 1;
                }

                $values .= '(';
                foreach($fieldline as $key => $value){
                    $values .= isset($line[$key]) ? ":bi{$index}," : "'',";
                    $param[":bi$index"] = trim($line[$key]);
                    $index++;
                }
                $values = rtrim($values, ',');
                $values .= '),';
            }
            $values = rtrim($values, ',');
            $sql .= $values; // sql 3

        }

        $query = $this->getDriver()->query($sql, $param);
        if (false === $query) {
            return false;
        } else {
            return $this->getDriver()->rowCount($query);
        }
    }

    /**
     * 开始一个事务
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->getDriver()->beginTransaction();
    }

    /**
     * 提交事务
     *
     * @return bool
     */
    public function commit()
    {
        return $this->getDriver()->commit();
    }

    /**
     * 回滚事务
     *
     * @return bool
     */
    public function rollBack()
    {
        return $this->getDriver()->rollBack();
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
        return $this->getDriver()->transaction($closure);
    }

    /**
     * 取表字段
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * 准备数据
     *
     * @param array $array
     * @return array
     */
    protected function prepareData($array = null)
    {
        if (empty($array) or !is_array($array)) {
            return [];
        }

        $array = array_change_key_case($array, CASE_LOWER);

        $fields = $this->getFields();
        if (empty($fields) or !is_array($fields)) {
            return $array;
        }

        $data = [];
        foreach ($fields as $field) {
            if (isset($array[$field]) and is_scalar($array[$field])) {
                $data[$field] = trim($array[$field]);
            }
        }
        return $data;
    }

    /**
     * 设置查询字段
     *
     * @param str|array $field
     * @return this
     */
    public function field($field = null)
    {
        $this->getQuery()->field($field);

        return $this;
    }

    /**
     * 设置表名
     *
     * @return this
     */
    public function name($name = '')
    {
        $this->getQuery()->from($name);

        return $this;
    }

    /**
     * 取表名
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * getLastId
     *
     * @return int
     */
    public function getLastId()
    {
        return $this->getDriver()->lastId();
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

    /**
     * getMaster
     *
     * @return resource
     */
    public function getMaster()
    {
        return $this->getDriver()->getMaster();
    }

    /**
     * getSlave
     *
     * @return resource
     */
    public function getSlave()
    {
        return $this->getDriver()->getSlave();
    }

    /**
     * getPdo
     *
     * @return PDO
     */
    public function getPdo()
    {
        return $this->getDriver()->getPdo();
    }

    /**
     * debug
     *
     * @param bool $debug
     * @return void
     */
    public function debug($debug = true)
    {
        $this->getDriver()->debug($debug);

        return $this;
    }

}
