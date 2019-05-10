<?php

/**
 * MemcacheSessionHandler
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

use SessionHandlerInterface;
use Cuber\Memcache\MemcacheManager;

class MemcacheSessionHandler implements SessionHandlerInterface
{

    /**
     * cache
     *
     * @var Cuber\Memcache\MemcacheManager
     */
    private $cache;

    /**
     * 过期秒数
     *
     * @var int
     */
    private $expire;

    /**
     * 创建驱动
     *
     * @param  MemcacheManager  $cache
     * @param  array  $config
     * @return void
     */
    public function __construct(MemcacheManager $cache, $config = [])
    {
        $this->cache = $cache->connect(array_get($config, 'connect', 'session'));
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
        return $this->cache->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        return $this->cache->set($id, $data, $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}
