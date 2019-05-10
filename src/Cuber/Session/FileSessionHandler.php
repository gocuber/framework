<?php

/**
 * FileSessionHandler
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

use SessionHandlerInterface;
use Cuber\FileCache\FileCache;

class FileSessionHandler implements SessionHandlerInterface
{

    /**
     * FileCache
     *
     * @var Cuber\FileCache\FileCache
     */
    private $file;

    /**
     * 过期秒数
     *
     * @var int
     */
    private $expire;

    /**
     * 创建驱动
     *
     * @param  FileCache  $file
     * @param  array  $config
     * @return void
     */
    public function __construct(FileCache $file, $config = [])
    {
        $this->file = $file->connect(array_get($config, 'connect', 'session'));
        $this->expire = array_get($config, 'expire', 86400 * 7);
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
        return $this->file->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        return $this->file->set($id, $data, $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        return $this->file->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}
