<?php

/**
 * CacheSessionHandler
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

use SessionHandlerInterface;
use Cuber\Memcache\MemcacheManager;

class CacheSessionHandler implements SessionHandlerInterface
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
     * 前缀
     *
     * @var string
     */
    private $prefix = 'CUBERSESS_';

    /**
     * 创建驱动
     *
     * @param  MemcacheManager  $cache
     * @param  int  $expire
     * @return void
     */
    public function __construct(MemcacheManager $cache, $expire = 86400 * 7)
    {
        $this->cache = $cache;
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
        return $this->cache->get($this->prefix . $id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        return $this->cache->set($this->prefix . $id, $data, $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        return $this->cache->delete($this->prefix . $id);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}
