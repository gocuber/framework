<?php

/**
 * FileSessionHandler
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

use SessionHandlerInterface;

class FileSessionHandler implements SessionHandlerInterface
{

    /**
     * file
     *
     * @var Cuber\Cache\File
     */
    private $file;

    /**
     * 过期秒数
     *
     * @var int
     */
    private $expire;

    /**
     * 前缀
     *
     * @var string
     */
    private $prefix = 'CUBERSESS_';

    /**
     * 创建驱动
     *
     * @param  File  $file
     * @param  int  $expire
     * @return void
     */
    public function __construct(File $file, $expire = 86400 * 7)
    {
        $this->file = $file;
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
        return $this->file->get($this->prefix . $id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        return $this->file->set($this->prefix . $id, $data, $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        return $this->file->delete($this->prefix . $id);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}
