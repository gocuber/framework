<?php

/**
 * MysqlSessionHandler
 * 
 * 创建表 字段：
 * 
 * id
 * data
 * expire
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

use SessionHandlerInterface;
use Cuber\Support\Facades\DB;

class MysqlSessionHandler implements SessionHandlerInterface
{

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
    private $table = 'app_session';

    /**
     * 创建驱动
     *
     * @param  int  $expire
     * @return void
     */
    public function __construct($expire = 86400 * 7)
    {
        $this->expire = $expire;
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
        $data = DB::name($this->table)->where(['id'=>$id])->line();

        if (0 == $data['expire'] or $data >= $_SERVER['REQUEST_TIME']) {
            return json_decode($data['data'], true);
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        $sql = "insert into " . $this->table . " (id,data,expire) values (':id',':data',:expire) on ...";
        DB::query($sql, ['id'=>$id, 'data'=>json_encode($data), 'expire'=>$this->expire]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        return DB::name($this->table)->where(['id'=>$id])->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}
