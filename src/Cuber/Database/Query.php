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

    private $cond = null;

    private $sql = null;

    private $name = null; // model 使用

    private $index = null;

    /**
     * autoCond
     *
     * @param array|str $cond
     * @param str $sign
     *
     * @return array
     */
    private function autoCond($cond = null, $sign = 'and')
    {
        if (empty($cond)) {
            return null;
        }

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
     * @param array|str $cond
     * @param str $sign
     *
     * @return bool
     */
    private function mergeCond($cond = null, $sign = 'and')
    {
        if (empty($cond) or !is_array($cond) or !isset($cond[0]) or !in_array($cond[0], ['and', 'or'], true)) {
            return false;
        }

        if (empty($this->cond)) {
            $this->cond = $cond;
        }

        $_sign    = $this->cond[0];
        $sub_sign = $cond[0];

        if ($sign == $_sign) {
            if ($sign == $sub_sign) {
                unset($cond[0]);
                $this->cond = array_merge($this->cond, $cond);
            } else {
                $this->cond[] = $cond;
            }
        } else {
            if ($sign == $sub_sign) {
                unset($cond[0]);
                $this->cond = array_merge([$sign, $this->cond], $cond);
            } else {
                $this->cond = [$sign, $this->cond, $cond];
            }
        }

        return true;
    }

    /**
     * where
     *
     * @param array|str $cond
     *
     * @return bool
     */
    public function where($cond = null)
    {
        if (empty($cond)) {
            return false;
        }

        $this->cond = $this->autoCond($cond);
        return true;
    }

    /**
     * andWhere
     *
     * @param array|str $cond
     *
     * @return bool
     */
    public function andWhere($cond = null)
    {
        if (empty($cond)) {
            return false;
        }

        return $this->mergeCond($this->autoCond($cond));
    }

    /**
     * orWhere
     *
     * @param array|str $cond
     *
     * @return bool
     */
    public function orWhere($cond = null)
    {
        if (empty($cond)) {
            return $this;
        }

        return $this->mergeCond($this->autoCond($cond), 'or');
    }

    /**
     * buildCond
     *
     * @param array $cond
     *
     * @return str
     */
    private function buildCond($cond = null)
    {
        if (empty($cond) or !is_array($cond) or !isset($cond[0]) or !in_array($cond[0], ['and', 'or'], true)) {
            return '';
        }

        $sign = $cond[0];
        unset($cond[0]);

        $sql = '';
        foreach ($cond as $key => $value) {
            if (isset($value[0]) and in_array($value[0], ['and', 'or'], true)) {
                $sql .= ' ' . $sign . ' (' . $this->buildCond($value) . ')';
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
        return "$key $sign ($in)";
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
        return "$key=" . $this->setParam($value);
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

        return "$key $sign " . $this->setParam($value[0]) . " and " . $this->setParam($value[1]);
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

        if (count($value) == 4 and in_array($value[1], ['between', 'not between'], true)) {
            return $this->buildCondBetween($value[0], [$value[2], $value[3]], $value[1]);
        } elseif (count($value) == 3 and in_array($value[1], ['between', 'not between'], true)) {
            return $this->buildCondBetween($value[0], $value[2], $value[1]);
        } elseif (count($value) == 3 and in_array($value[1], ['in', 'not in'], true)) {
            return $this->buildCondIn($value[0], $value[2], $value[1]);
        } elseif (count($value) == 3) {
            return $value[0] . ' ' . $value[1] . ' ' . $this->setParam($value[2]); // > < <> like ['name','like','%key%'] ['name','like','key%']
        } elseif (count($value) == 2 and is_array($value[1])) {
            return $this->buildCondIn($value[0], $value[1]);
        } elseif (count($value) == 2) {
            return $value[0] . '=' . $this->setParam($value[1]);
        } else {
            return '';
        }
    }

    /**
     * setParam
     *
     * @param string $param
     *
     * @return string
     */
    private function setParam($param = '')
    {
        if (isset($this->index)) {
            $this->index++;
        } else {
            $this->index = 1;
        }

        $key = ':p' . $this->index;
        $this->sql['param'][$key] = $param;
        return $key;
    }

    /**
     * 取完整的sql
     *
     * @return array
     */
    public function getSql()
    {
        $this->sql['where'] = $this->buildCond($this->cond);
        extract($this->sql);

        $sql = "select " . (isset($field) ? $field : '*') . " from " . (empty($from) ? $this->name : $from);

        isset($join)    and $sql .= " $join";
        isset($where) and '' !== $where and $sql .= " where $where";
        isset($groupby) and $sql .= " group by $groupby";
        isset($having)  and $sql .= " having $having";
        isset($orderby) and $sql .= " order by $orderby";
        isset($limit)   and $sql .= " limit " . (empty($offset) ? $limit : ($offset . ',' . $limit));

        $this->sql['sql'] = $sql;
        isset($this->sql['param']) or $this->sql['param'] = null;

        $ret = $this->sql;
        $this->cond  = null;
        $this->sql   = null;
        $this->index = null;
        return $ret;
    }

    /**
     * 设置查询字段
     *
     * @param str|array $field
     *
     * @return bool
     */
    public function field($field = null)
    {
        if (empty($field)) {
            return false;
        }

        $this->sql['field'] = is_array($field) ? implode(',', $field) : trim($field);
        return true;
    }

    /**
     * 设置表名
     *
     * @param str $name
     *
     * @return bool
     */
    public function from($name = null)
    {
        if (empty($name)) {
            return false;
        }

        $this->sql['from'] = trim($name);
        return true;
    }

    /**
     * 设置join
     *
     * @param str $type   inner join|left join|right join
     * @param str $table  user
     * @param str $on     a.gid=b.gid
     *
     * @return bool
     */
    public function join($type = null, $table = null, $on = null)
    {
        if (empty($type) or empty($table) or empty($on)) {
            return false;
        }

        if (isset($this->sql['join'])) {
            $this->sql['join'] .= '' . $type . $table . ' on ' . $on;
        } else {
            $this->sql['join'] = $type . $table . ' on ' . $on;
        }

        return true;
    }

    /**
     * 设置 group by
     *
     * @param str|array $cond
     *
     * @return bool
     */
    public function groupBy($cond = null)
    {
        if (empty($cond)) {
            return false;
        }

        $this->sql['groupby'] = is_array($cond) ? implode(',', $cond) : trim($cond);
        return true;
    }

    /**
     * 设置 having
     *
     * @param str $cond
     *
     * @return bool
     */
    public function having($cond = null)
    {
        if (empty($cond)) {
            return false;
        }

        $this->sql['having'] = trim($cond);
        return true;
    }

    /**
     * 设置排序条件
     *
     * @param str|array $cond
     *
     * @return bool
     */
    public function orderBy($cond = null)
    {
        if (empty($cond)) {
            return false;
        }

        if (is_array($cond)) {
            $by = '';
            foreach ($cond as $key=>$value) {
                if (is_int($key)) {
                    $by .= $value . ',';
                } else {
                    $value = ('desc'==$value) ? 'desc' : 'asc';
                    $by .= "$key {$value},";
                }
            }
            $this->sql['orderby'] = trim($by, ',');
        } else {
            $this->sql['orderby'] = $cond;
        }

        return true;
    }

    /**
     * offset
     *
     * @param int $offset
     * @return bool
     */
    public function offset($offset = 0)
    {
        $this->sql['offset'] = (int)$offset;
        return true;
    }

    /**
     * limit
     *
     * @param int $limit
     * @return bool
     */
    public function limit($limit = 0)
    {
        $this->sql['limit'] = (int)$limit;
        return true;
    }

    /**
     * page
     *
     * @param int $currpage
     * @param int $pagesize
     *
     * @return bool
     */
    public function page($currpage = 1, $pagesize = 1)
    {
        $currpage = (int)$currpage < 1 ? 1 : (int)$currpage;
        $pagesize = (int)$pagesize < 0 ? 0 : (int)$pagesize;

        $this->sql['offset'] = ($currpage - 1) * $pagesize;
        $this->sql['limit']  = $pagesize;

        return true;
    }

    /**
     * 设置表名 model 使用
     *
     * @return bool
     */
    public function name($name = '')
    {
        $this->name = $name;
        return true;
    }

}
