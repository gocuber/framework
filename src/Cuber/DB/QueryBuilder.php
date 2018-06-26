<?php

/**
 * DB_QueryBuilder
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class DB_QueryBuilder
{

    private $_cond = null;

    private $_sql = null;

    private $_name = null; // model 使用

    private $_index = null;

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
        if(empty($cond)){
            return null;
        }

        if(is_array($cond)){
            if(!(isset($cond[0]) and in_array($cond[0], array('and','or')))){
                $cond = array_merge(array($sign), $cond);
            }
        }else{
            $cond = array($sign, $cond);
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
        if(empty($cond) or !is_array($cond) or !isset($cond[0]) or !in_array($cond[0], array('and','or'))){
            return false;
        }

        if(empty($this->_cond)){
            $this->_cond = $cond;
        }

        $_sign    = $this->_cond[0];
        $sub_sign = $cond[0];

        if($sign == $_sign){
            if($sign == $sub_sign){
                unset($cond[0]);
                $this->_cond = array_merge($this->_cond, $cond);
            }else{
                $this->_cond[] = $cond;
            }
        }else{
            if($sign == $sub_sign){
                unset($cond[0]);
                $this->_cond = array_merge(array($sign,$this->_cond), $cond);
            }else{
                $this->_cond = array($sign,$this->_cond,$cond);
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
        if(empty($cond)){
            return false;
        }

        $this->_cond = $this->autoCond($cond);
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
        if(empty($cond)){
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
        if(empty($cond)){
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
        if(empty($cond) or !is_array($cond) or !isset($cond[0]) or !in_array($cond[0], array('and','or'))){
            return '';
        }

        $sign = $cond[0];
        unset($cond[0]);

        $sql = '';
        foreach($cond as $key => $value){
            if(isset($value[0]) and in_array($value[0], array('and','or'))){
                $sql .= ' ' . $sign . ' (' . $this->buildCond($value) . ')';
            }else{
                if(is_int($key)){
                    if(is_array($value)){
                        if(!isset($value[0])){
                            foreach($value as $_k => $_v){break 1;}
                            $sql .= ' ' . $sign . ' ' . $this->buildCondHash($_k, $_v);
                        }else{
                            $sql .= ' ' . $sign . ' ' . $this->buildCondArray($value);
                        }
                    }else{
                        $sql .= ' ' . $sign . ' ' . $this->buildCondStr($value);
                    }
                }else{
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
        if(empty($value) or !is_array($value)){
            return '';
        }

        $in = '';
        foreach($value as $k=>$v){
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
        if(is_array($value)){
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
        if(!is_array($value) or !isset($value[0]) or !isset($value[1])){
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
        if(empty($value) or !is_array($value)){
            return '';
        }

        if(count($value)==4 and in_array($value[1], array('between','not between'))){
            return $this->buildCondBetween($value[0], array($value[2], $value[3]), $value[1]);
        }elseif(count($value)==3 and in_array($value[1], array('between','not between'))){
            return $this->buildCondBetween($value[0], $value[2], $value[1]);
        }elseif(count($value)==3 and in_array($value[1], array('in','not in'))){
            return $this->buildCondIn($value[0], $value[2], $value[1]);
        }elseif(count($value)==3){
            return $value[0] . ' ' . $value[1] . ' ' . $this->setParam($value[2]); // > < <> like ['name','like','%key%'] ['name','like','key%']
        }elseif(count($value)==2 and is_array($value[1])){
            return $this->buildCondIn($value[0], $value[1]);
        }elseif(count($value)==2){
            return $value[0] . '=' . $this->setParam($value[1]);
        }else{
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
        if(isset($this->_index)){
            $this->_index++;
        }else{
            $this->_index = 1;
        }

        $key = ':p' . $this->_index;
        $this->_sql['param'][$key] = $param;
        return $key;
    }

    /**
     * 取完整的sql
     *
     * @return array
     */
    public function getSql()
    {
        $this->_sql['where'] = $this->buildCond($this->_cond);
        extract($this->_sql);

        $sql = "select " . (isset($field) ? $field : '*') . " from " . (empty($from) ? $this->_name : $from);

        !empty($join)    and $sql .= " $join";
        !empty($where)   and $sql .= " where $where";
        !empty($groupby) and $sql .= " group by $groupby";
        !empty($having)  and $sql .= " having $having";
        !empty($orderby) and $sql .= " order by $orderby";
        !empty($limit)   and $sql .= " limit " . (empty($offset) ? $limit : ($offset . ',' . $limit));

        $this->_sql['sql'] = $sql;
        isset($this->_sql['param']) or $this->_sql['param'] = null;

        $ret = $this->_sql;
        $this->_cond  = null;
        $this->_sql   = null;
        $this->_index = null;
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
        if(empty($field)){
            return false;
        }

        $this->_sql['field'] = is_array($field) ? implode(',', $field) : trim($field);
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
        if(empty($name)){
            return false;
        }

        $this->_sql['from'] = trim($name);
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
        if(empty($type) or empty($table) or empty($on)){
            return false;
        }

        if(isset($this->_sql['join'])){
            $this->_sql['join'] .= '' . $type . $table . ' on ' . $on;
        }else{
            $this->_sql['join'] = $type . $table . ' on ' . $on;
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
        if(empty($cond)){
            return false;
        }

        $this->_sql['groupby'] = is_array($cond) ? implode(',', $cond) : trim($cond);
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
        if(empty($cond)){
            return false;
        }

        $this->_sql['having'] = trim($cond);
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
        if(empty($cond)){
            return false;
        }

        if(is_array($cond)){
            $by = '';
            foreach($cond as $key=>$value){
                if(is_int($key)){
                    $by .= $value . ',';
                }else{
                    $value = ('desc'==$value) ? 'desc' : 'asc';
                    $by .= "$key {$value},";
                }
            }
            $this->_sql['orderby'] = trim($by, ',');
        }else{
            $this->_sql['orderby'] = $cond;
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
        $this->_sql['offset'] = round($offset, 0);
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
        $this->_sql['limit'] = round($limit, 0);
        return true;
    }

    /**
     * page
     *
     * @param number $currpage
     * @param number $pagesize
     *
     * @return bool
     */
    public function page($currpage = 1, $pagesize = 1)
    {
        $currpage = round($currpage, 0);
        $pagesize = round($pagesize, 0);

        $this->_sql['offset'] = ($currpage-1)*$pagesize;
        $this->_sql['limit']  = $pagesize;

        return true;
    }

    /**
     * 设置表名 model 使用
     *
     * @return bool
     */
    public function name($name = '')
    {
        $this->_name = $name;
        return true;
    }

}
