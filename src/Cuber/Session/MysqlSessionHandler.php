<?php

/**
 * MysqlSessionHandler
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
        $this->table = array_get($config, 'table', 'app_session');
    }

    public function open($save_path, $session_name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $session = $this->db->name($this->table)->where(['id'=>$id])->line();

        if (!empty($session) and (0 == $session['expire'] or $session['expire'] >= time())) {
            return $session['data'];
        } else {
            return null;
        }
    }

    public function write($id, $data)
    {
        $time = time();
        $expire = (0 == $this->expire) ? 0 : $time + $this->expire;

        $this->db->name($this->table)
            ->duplicate(['data'=>'data', 'expire'=>'expire', 'utime'=>'ctime'])
            ->insert(['id'=>$id, 'data'=>$data, 'expire'=>$expire, 'ctime'=>$time]);

        return true;
    }

    public function destroy($id)
    {
        return $this->db->name($this->table)->where(['id'=>$id])->delete();
    }

    public function gc($maxlifetime)
    {
        return true;
    }

}
