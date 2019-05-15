<?php

/**
 * Mysql
 *
 * 表字段 key value expire ctime utime
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

use Cuber\Database\DatabaseManager;

class Mysql implements Store
{

    private $db;

    private $table;

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function config($config)
    {
        $this->table = array_get($config, 'table', 'app_cache');

        return $this;
    }

    public function connect($key)
    {
        $this->db->connect($key);

        return $this;
    }

    public function set($key = null, $value = null, $expire = 0)
    {
        $time = time();
        $expire = (0 == $expire) ? 0 : $time + $expire;
        $sql = "insert into " . $this->table . " (`key`,`value`,expire,ctime) values (:key,:value,:expire,:ctime)"
            . " on duplicate key update `value`=values(`value`),expire=values(expire),utime=values(ctime)";

        $this->db->query($sql, ['key'=>$key, 'value'=>$value, 'expire'=>$expire, 'ctime'=>$time]);

        return true;
    }

    public function get($key = null, $default = null)
    {
        $data = $this->db->name($this->table)->where(['key'=>$key])->line();

        if (!empty($data) and (0 == $data['expire'] or $data['expire'] >= time())) {
            return $data['value'];
        } else {
            return $default;
        }
    }

    public function delete($key = null)
    {
        return $this->db->name($this->table)->where(['key'=>$key])->delete();
    }

    public function setMulti(array $keys = [], $expire = 0)
    {
        $time = time();
        $expire = (0 == $expire) ? 0 : $time + $expire;
        $params = ['expire'=>$expire, 'ctime'=>$time];
        $values = '';
        $index = 1;
        foreach ($keys as $key => $value) {
            $values .= "(:key{$index},:value{$index},:expire,:ctime),";
            $params["key$index"] = $key;
            $params["value$index"] = $value;
            $index++;
        }
        $values = rtrim($values, ',');
        $sql = "insert into " . $this->table . " (`key`,`value`,expire,ctime) values $values"
            . " on duplicate key update `value`=values(`value`),expire=values(expire),utime=values(ctime)";

        $this->db->query($sql, $params);

        return true;
    }

    public function getMulti(array $keys = [])
    {
        $data = $this->db->name($this->table)->where(['key'=>$keys])->hash('key');

        if (empty($data)) {
            return null;
        }

        $cache = [];
        foreach ($data as $key=>$value) {
            if (0 == $value['expire'] or $value['expire'] >= time()) {
                $cache[$key] = $value['value'];
            }
        }

        return empty($cache) ? null : $cache;
    }

    public function deleteMulti(array $keys = [])
    {
        return $this->db->name($this->table)->where(['id'=>$keys])->delete();
    }

    public function increment($key = null, $value = 1)
    {
        $time = time();
        $value = (int)$value;
        $expire = 0;
        $sql = "insert into " . $this->table . " (`key`,`value`,expire,ctime) values (:key,:value,:expire,:ctime)"
            . " on duplicate key update `value`=`value`+values(`value`),utime=values(ctime)";

        $this->db->query($sql, ['key'=>$key, 'value'=>$value, 'expire'=>$expire, 'ctime'=>$time]);

        return true;
    }

    public function decrement($key = null, $value = 1)
    {
        return $this->increment($key, 0 - $value);
    }

}
