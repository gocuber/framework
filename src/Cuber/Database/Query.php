<?php

/**
 * Query
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Database;

class Query
{

    private $binds = null;

    private $result = ['sql'=>'', 'param'=>[]];

    /**
     * autoCond
     *
     * @param  array|string  $cond
     * @param  string        $sign
     *
     * @return array
     */
    private function autoCond($cond, $sign = 'and')
    {
        if (is_array($cond)) {
            if (!(isset($cond[0]) and in_array($cond[0], ['and', 'or'], true))) {
                $cond = array_merge([$sign], $cond);
            }
        } else {
            $cond = [$sign, $cond];
        }

        return $cond;
    }

    /**
     * mergeCond
     *
     * @param  array   $cond
     * @param  string  $sign
     *
     * @return bool
     */
    private function mergeCond(array $cond, $sign = 'and')
    {
        if (empty($this->binds['cond'])) {
            $this->binds['cond'] = $cond;
        } else {
            $this->binds['cond'] = [$sign, $this->binds['cond'], $cond];
        }
        return true;

        $_sign = $this->binds['cond'][0];
        $sub_sign = $cond[0];

        if ($sign == $_sign) {
            if ($sign == $sub_sign) {
                unset($cond[0]);
                $this->binds['cond'] = array_merge($this->binds['cond'], $cond);
            } else {
                $this->binds['cond'][] = $cond;
            }
        } else {
            if ($sign == $sub_sign) {
                unset($cond[0]);
                $this->binds['cond'] = array_merge([$sign, $this->binds['cond']], $cond);
            } else {
                $this->binds['cond'] = [$sign, $this->binds['cond'], $cond];
            }
        }

        return true;
    }

    /**
     * where
     *
     * @param array|string $cond
     *
     * @return $this
     */
    public function where($cond)
    {
        if (empty($cond)) {
            return $this;
        }

        $this->binds['cond'] = $this->autoCond($cond);
        return $this;
    }

    /**
     * andWhere
     *
     * @param array|string $cond
     *
     * @return $this
     */
    public function andWhere($cond)
    {
        if (empty($cond)) {
            return $this;
        }

        $this->mergeCond($this->autoCond($cond));

        // $this->binds['cond'] = ['and', $this->binds['cond'], $this->autoCond($cond)];
        return $this;
    }

    /**
     * orWhere
     *
     * @param array|string $cond
     *
     * @return $this
     */
    public function orWhere($cond)
    {
        if (empty($cond)) {
            return $this;
        }

        $this->mergeCond($this->autoCond($cond), 'or');

        // $this->binds['cond'] = ['or', $this->binds['cond'], $this->autoCond($cond)];
        return $this;
    }

    /**
     * buildCond
     *
     * @param array $cond
     *
     * @return string
     */
    private function buildCond($cond = null)
    {
        if (!isset($cond[0]) or !in_array($cond[0], ['and', 'or'], true)) {
            return '';
        }

        $sign = $cond[0];
        unset($cond[0]);

        $sql = '';
        foreach ($cond as $key => $value) {
            if (isset($value[0]) and in_array($value[0], ['and', 'or'], true)) {
                if ($sign == $value[0]) {
                    $sql .= ' ' . $sign . ' ' . $this->buildCond($value);
                } else {
                    $sql .= ' ' . $sign . ' (' . $this->buildCond($value) . ')';
                }
            } else {
                if (is_int($key)) {
                    if (is_array($value)) {
                        if (!isset($value[0])) {
                            foreach ($value as $_k => $_v) { break 1; }
                            $sql .= ' ' . $sign . ' ' . $this->buildCondHash($_k, $_v);
                        } else {
                            $sql .= ' ' . $sign . ' ' . $this->buildCondArray($value);
                        }
                    } else {
                        $sql .= ' ' . $sign . ' ' . $this->buildCondStr($value);
                    }
                } else {
                    $sql .= ' ' . $sign . ' ' . $this->buildCondHash($key, $value);
                }
            }
        }

        return substr($sql, strlen($sign) + 2);
    }

    /**
     * buildCondIn
     *
     * @param string $key
     * @param array $value
     * @param string $sign
     *
     * @return string
     */
    private function buildCondIn($key = null, $value = null, $sign = 'in')
    {
        if (empty($value) or !is_array($value)) {
            return '0';
        }

        $in = '';
        foreach ($value as $k=>$v) {
            $in .= $this->setParam($v) . ',';
        }
        $in = rtrim($in, ',');
        return "`$key` $sign ($in)";
    }

    /**
     * buildCondHash
     *
     * @param string $key
     * @param string|array $value
     *
     * @return string
     */
    private function buildCondHash($key = null, $value = null)
    {
        if (is_array($value)) {
            return $this->buildCondIn($key, $value);
        }

        return "`$key`=" . $this->setParam($value);
    }

    /**
     * buildCondStr
     *
     * @param string $value
     *
     * @return string
     */
    private function buildCondStr($value = null)
    {
        // return "($value)";
        return $value;
    }

    /**
     * buildCondBetween
     *
     * @param string $key
     * @param array $value
     * @param string $sign
     *
     * @return string
     */
    private function buildCondBetween($key = null, $value = null, $sign = 'between')
    {
        if (!is_array($value) or !isset($value[0]) or !isset($value[1])) {
            return '';
        }

        return "`$key` $sign " . $this->setParam($value[0]) . " and " . $this->setParam($value[1]);
    }

    /**
     * buildCondArray
     *
     * @param array $value
     *
     * @return string
     */
    private function buildCondArray($value = null)
    {
        if (empty($value) or !is_array($value)) {
            return '';
        }

        if (count($value) == 3 and in_array($value[1], ['between', 'not between'], true)) {
            return $this->buildCondBetween($value[0], $value[2], $value[1]);
        } elseif (count($value) == 3 and in_array($value[1], ['in', 'not in'], true)) {
            return $this->buildCondIn($value[0], $value[2], $value[1]);
        } elseif (count($value) == 3) {
            return '`' . $value[0] . '` ' . $value[1] . ' ' . $this->setParam($value[2]); // > < <> like ['name','like','%key%'] ['name','like','key%']
        } elseif (count($value) == 2 and is_array($value[1])) {
            return $this->buildCondIn($value[0], $value[1]);
        } elseif (count($value) == 2) {
            return '`' . $value[0] . '`=' . $this->setParam($value[1]);
        } else {
            return '';
        }
    }

    /**
     * buildField
     *
     * @return string
     */
    private function buildField()
    {
        return isset($this->binds['field']) ? $this->binds['field'] : '*';
    }

    /**
     * setParam
     *
     * @param string $value
     *
     * @return string
     */
    private function setParam($value = '')
    {
        $i = isset($this->result['param']) ? count($this->result['param']) : 0;
        $key = ':p' . ++$i;
        $this->result['param'][$key] = $value;
        return $key;
    }

    private function onSelect()
    {
        $this->result['sql'] = 'select ' . $this->buildField() . ' from ' . $this->binds['from'];

        return $this;
    }

    public function onInsert()
    {
        $this->result['sql'] = 'insert into `' . $this->binds['from'] . '` ' . $this->binds['insert_values'];

        return $this;
    }

    public function onBatchInsert()
    {
        $this->result['sql'] = 'insert into `' . $this->binds['from'] . '` ' . $this->binds['batchinsert_values'];

        return $this;
    }

    public function onUpdate()
    {
        $this->result['sql'] = "update `" . $this->binds['from'] . "` set " . $this->binds['update_fields'];

        return $this;
    }

    public function onDelete()
    {
        $this->result['sql'] = "delete from `" . $this->binds['from'] . "`";

        return $this;
    }

    private function onJoin()
    {
        if (isset($this->binds['join'])) {
            $this->result['sql'] .= ' ' . $this->binds['join'];
        }

        return $this;
    }

    private function onWhere()
    {
        if (isset($this->binds['cond'])) {s($this->binds['cond']);
            $where = $this->buildCond($this->binds['cond']);
            if ('' !== $where) {
                $this->result['sql'] .= " where $where";
            }
        }

        return $this;
    }

    private function onGroupBy()
    {
        if (isset($this->binds['groupby'])) {
            $this->result['sql'] .= ' group by ' . $this->binds['groupby'];
        }

        return $this;
    }

    private function onHaving()
    {
        if (isset($this->binds['having'])) {
            $this->result['sql'] .= ' having ' . $this->binds['having'];
        }

        return $this;
    }

    private function onOrderBy()
    {
        if (isset($this->binds['orderby'])) {
            $this->result['sql'] .= ' order by ' . $this->binds['orderby'];
        }

        return $this;
    }

    private function onLimit()
    {
        if (isset($this->binds['limit'])) {
            $this->result['sql'] .= ' limit ' . (empty($this->binds['offset']) ? $this->binds['limit'] : ($this->binds['offset'] . ',' . $this->binds['limit']));
        }

        return $this;
    }

    private function onDuplicate()
    {
        if (isset($this->binds['duplicate'])) {
            $this->result['sql'] .= ' on duplicate key update ' . $this->binds['duplicate'];
        }

        return $this;
    }

    public function result()
    {
        return $this->result;
    }

    public function buildSelect()
    {
        $result = $this->onSelect()->onJoin()->onWhere()->onGroupBy()->onHaving()->onOrderBy()->onLimit()->result();

        $this->flush();
        return $result;
    }

    public function buildInsert()
    {
        $result = $this->onInsert()->onDuplicate()->result();

        $this->flush();
        return $result;
    }

    public function buildBatchInsert()
    {
        $result = $this->onBatchInsert()->onDuplicate()->result();

        $this->flush();
        return $result;
    }

    public function buildUpdate($data = [])
    {
        $result = $this->onUpdate()->onWhere()->onOrderBy()->onLimit()->result();

        $this->flush();
        return $result;
    }

    public function buildDelete()
    {
        $result = $this->onDelete()->onWhere()->onOrderBy()->onLimit()->result();

        $this->flush();
        return $result;
    }

    /**
     * insert
     *
     * @return $this
     */
    public function insert($data = [])
    {
        if (!empty($data) and is_array($data)) {
            $fields = $values = '';
            foreach ($data as $key => $value) {
                $fields .= "`" . trim($key) . "`,";
                $values .= $this->setParam($value) . ',';
            }
            $fields = rtrim($fields, ',');
            $values = rtrim($values, ',');
            $sql = "({$fields}) values ({$values})";
        } else {
            $sql = "() values ()";
        }

        $this->binds['insert_values'] = $sql;
        return $this;
    }

    /**
     * batchInsert
     *
     * @return $this
     */
    public function batchInsert($data = [])
    {
        $field = '';
        $fields = array_keys(current($data));
        foreach ($fields as $f) {
            $field .= "`" . trim($f) . "`,";
        }
        $field = rtrim($field, ',');
        $sql = " ({$field}) values "; // sql 2

        $values = '';
        foreach ($data as $line) {
            $values .= '(';
            foreach ($fields as $field) {
                $value = isset($line[$field]) ? $line[$field] : '';
                $values .= $this->setParam($value) . ',';
            }
            $values = rtrim($values, ',');
            $values .= '),';
        }
        $values = rtrim($values, ',');
        $sql .= $values; // sql 3

        $this->binds['batchinsert_values'] = $sql;
        return $this;
    }

    /**
     * update
     *
     * @return $this
     */
    public function update($data = [])
    {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "`" . trim($key) . "`=" . $this->setParam($value) . ",";
        }
        $fields = rtrim($fields, ',');

        $this->binds['update_fields'] = $fields;
        return $this;
    }

    /**
     * 复写 重复再写数据
     *
     * @param str|array $data
     *
     * @return $this
     */
    public function duplicate($data = null)
    {
        if (empty($data)) {
            return $this;
        }

        if (is_array($data)) {
            $update = '';
            foreach ($data as $key => $value) {
                $update .= "`{$key}`=values(`{$value}`),";
            }
            $this->binds['duplicate'] = rtrim($update, ',');
        } else {
            $this->binds['duplicate'] = $data;
        }

        return $this;
    }

    /**
     * 设置查询字段
     *
     * @param string|array $field
     *
     * @return $this
     */
    public function field($field)
    {
        if (empty($field)) {
            return $this;
        }

        $this->binds['field'] = is_array($field) ? implode(',', $field) : trim($field);
        return $this;
    }

    /**
     * 设置表名
     *
     * @param str $name
     *
     * @return $this
     */
    public function name($name = null)
    {
        if (empty($name)) {
            return $this;
        }

        $this->binds['from'] = trim($name);
        return $this;
    }

    /**
     * @see join()
     */
    public function innerJoin($table = null, $on = null)
    {
        return $this->join('inner join', $table, $on);
    }

    /**
     * @see join()
     */
    public function leftJoin($table = null, $on = null)
    {
        return $this->join('left join', $table, $on);
    }

    /**
     * @see join()
     */
    public function rightJoin($table = null, $on = null)
    {
        return $this->join('right join', $table, $on);
    }

    /**
     * 设置join
     *
     * @param  string  $type   inner join|left join|right join
     * @param  string  $table  user
     * @param  string  $on     a.gid=b.gid
     *
     * @return $this
     */
    public function join($type = null, $table = null, $on = null)
    {
        if (empty($type) or empty($table) or empty($on)) {
            return $this;
        }

        if (isset($this->binds['join'])) {
            $this->binds['join'] .= " $type $table on $on";
        } else {
            $this->binds['join'] = "$type $table on $on";
        }

        return $this;
    }

    /**
     * 设置 group by
     *
     * @param str|array $cond
     *
     * @return $this
     */
    public function groupBy($cond = null)
    {
        if (empty($cond)) {
            return $this;
        }

        $this->binds['groupby'] = is_array($cond) ? implode(',', $cond) : trim($cond);
        return $this;
    }

    /**
     * 设置 having
     *
     * @param str $cond
     *
     * @return $this
     */
    public function having($cond = null)
    {
        if (empty($cond)) {
            return $this;
        }

        $this->binds['having'] = trim($cond);
        return $this;
    }

    /**
     * 设置排序条件
     *
     * @param str|array $cond
     *
     * @return $this
     */
    public function orderBy($cond = null)
    {
        if (empty($cond)) {
            return $this;
        }

        if (is_array($cond)) {
            $by = '';
            foreach ($cond as $key=>$value) {
                if (is_int($key)) {
                    $by .= $value . ',';
                } else {
                    $value = ('desc'==$value) ? 'desc' : 'asc';
                    $by .= "$key $value,";
                }
            }
            $this->binds['orderby'] = trim($by, ',');
        } else {
            $this->binds['orderby'] = $cond;
        }

        return $this;
    }

    /**
     * offset
     *
     * @param int $offset
     * @return $this
     */
    public function offset($offset = 0)
    {
        $this->binds['offset'] = (int)$offset;

        return $this;
    }

    /**
     * limit
     *
     * @param int $limit
     * @return $this
     */
    public function limit($limit = 0)
    {
        $this->binds['limit'] = (int)$limit;

        return $this;
    }

    /**
     * page
     *
     * @param int $currpage
     * @param int $pagesize
     *
     * @return $this
     */
    public function page($currpage = 1, $pagesize = 1)
    {
        $currpage = (int)$currpage < 1 ? 1 : (int)$currpage;
        $pagesize = (int)$pagesize < 0 ? 0 : (int)$pagesize;

        $this->binds['offset'] = ($currpage - 1) * $pagesize;
        $this->binds['limit']  = $pagesize;

        return $this;
    }

    /**
     * Flush
     *
     * @return void
     */
    public function flush()
    {
        $this->binds = null;
        $this->result = ['sql'=>'', 'param'=>[]];
    }

}
