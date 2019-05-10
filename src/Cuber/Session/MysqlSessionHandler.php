<?php

/**
 * MysqlSessionHandler
 *
 * 表字段 id data expire ctime utime
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

use SessionHandlerInterface;
use Cuber\Database\DatabaseManager;

class MysqlSessionHandler implements SessionHandlerInterface
{

    /**
     * DatabaseManager
     *
     * @var Cuber\Database\DatabaseManager
     */
    private $db;

    /**
     * 过期秒数
     *
     * @var int
     */
    private $expire;

    /**
     * 存储Session数据的表名
     *
     * @var string
     */
    private $table;

    /**
     * 创建驱动
     *
     * @param  DatabaseManager $db
     * @param  array  $config
     * @return void
     */
    public function __construct(DatabaseManager $db, $config = [])
    {
        $this->db = $db->connect(array_get($config, 'connect', 'session'));
        $this->expire = array_get($config, 'expire', 86400 * 7);
        $this->table = array_get($config, 'table_name', 'app_session');
    }

    /**
     * {@inheritdoc}
     */
    public function open($save_path, $session_name)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($id)
    {
        $session = $this->db->name($this->table)->where(['id'=>$id])->line();

        if (!empty($session) and (0 == $session['expire'] or $session['expire'] >= time())) {
            return $session['data'];
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        $time = time();
        $expire = (0 == $this->expire) ? 0 : $time + $this->expire;
        $sql = "insert into " . $this->table . " (id,data,expire,ctime) values (:id,:data,:expire,:ctime)"
             . " on duplicate key update data=values(data),expire=values(expire),utime=values(ctime)";

        $this->db->query($sql, ['id'=>$id, 'data'=>$data, 'expire'=>$expire, 'ctime'=>$time]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        return $this->db->name($this->table)->where(['id'=>$id])->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}
