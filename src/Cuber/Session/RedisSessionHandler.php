<?php

/**
 * RedisSessionHandler
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Session;

use SessionHandlerInterface;
use Cuber\Redis\RedisManager;

class RedisSessionHandler implements SessionHandlerInterface
{

    /**
     * redis
     *
     * @var Cuber\Redis\RedisManager
     */
    private $redis;

    /**
     * 过期秒数
     *
     * @var int
     */
    private $expire;

    /**
     * 创建驱动
     *
     * @param  RedisManager  $redis
     * @param  array  $config
     * @return void
     */
    public function __construct(RedisManager $redis, $config = [])
    {
        $this->redis = $redis->connect(array_get($config, 'connect', 'session'));
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
        return $this->redis->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        if (0 == $this->expire) {
            return $this->redis->set($id, $data);
        } else {
            return $this->redis->set($id, $data, $this->expire);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        return $this->redis->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}
