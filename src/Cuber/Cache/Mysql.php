<?php

/**
 * Mysql
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

        $this->db->name($this->table)
            ->duplicate(['value'=>'value', 'expire'=>'expire', 'utime'=>'ctime'])
            ->insert(['key'=>$key, 'value'=>$value, 'expire'=>$expire, 'ctime'=>$time]);

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

    public function mSet(array $keys = [], $expire = 0)
    {
        $time = time();
        $expire = (0 == $expire) ? 0 : $time + $expire;

        $data = [];
        foreach ($keys as $key => $value) {
            $data[] = [
                'key' => $key,
                'value' => $value,
                'expire' => $expire,
                'ctime' => $time,
            ];
        }

        $this->db->name($this->table)
            ->duplicate(['value'=>'value', 'expire'=>'expire', 'utime'=>'ctime'])
            ->batchInsert($data);

        return true;
    }

    public function mGet(array $keys = [])
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

    public function mDelete(array $keys = [])
    {
        return $this->db->name($this->table)->where(['id'=>$keys])->delete();
    }

    public function increment($key = null, $value = 1)
    {
        $this->db->name($this->table)
            ->duplicate('`value`=`value`+values(`value`),`utime`=values(`ctime`)')
            ->insert(['key'=>$key, 'value'=>(int)$value, 'expire'=>0, 'ctime'=>time()]);

        return true;
    }

    public function decrement($key = null, $value = 1)
    {
        return $this->increment($key, 0 - $value);
    }

}
