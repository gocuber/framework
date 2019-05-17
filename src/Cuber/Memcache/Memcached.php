<?php

/**
 * Memcached
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Memcache;

class Memcached
{

    /**
     * 配置
     *
     * @var array
     */
    private $config;

    /**
     * 当前连接
     *
     * @var string
     */
    private $connect = 'default';

    /**
     * 连接
     *
     * @var array
     */
    private $conn;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 切换连接
     *
     * @return $this
     */
    public function connect($key = 'default')
    {
        $this->connect = $key;

        return $this;
    }

    /**
     * 建立连接
     *
     * @return \Memcached
     */
    private function conn()
    {
        if (!isset($this->conn[$this->connect])) {
            $config = array_get($this->config, 'connects.' . $this->connect, []);
            $key = md5(serialize($config));
            $mem = new \Memcached($key);
            if (isset($config[0]) and is_array($config[0])) {
                $conf = [];
                foreach ($config as $value) {
                    $conf[] = array_values($value);
                }
                $mem->addServers($conf);
            } else {
                $mem->addServer($config['host'], $config['port']);
            }
            $this->conn[$this->connect] = $mem;
        }

        return $this->conn[$this->connect];
    }

    /**
     * 关闭当前连接
     *
     * @return bool
     */
    public function close()
    {
        if (isset($this->conn[$this->connect])) {
            $this->conn[$this->connect]->quit();
            $this->conn[$this->connect] = null;
        }

        return true;
    }

    /**
     * 关闭全部连接
     *
     * @return bool
     */
    public function closeAll()
    {
        foreach ($this->conn as $conn=>$obj) {
            $this->conn[$conn]->quit();
        }
        $this->conn = null;

        return true;
    }

    /**
     * set
     *
     * @param string $key
     * @param string $value
     * @param int $time
     * @return bool
     */
    public function set($key = '', $value = '', $time = 0)
    {
        return $this->conn()->set($key, $value, (0 == $time) ? 0 : time() + $time);
    }

    /**
     * get
     *
     * @param string $key
     * @return string
     */
    public function get($key = '')
    {
        return $this->conn()->get($key);
    }

    /**
     * delete
     *
     * @param string $key
     * @param int $time
     * @return bool
     */
    public function delete($key = '', $time = 0)
    {
        return $this->conn()->delete($key, $time);
    }

    /**
     * setMulti
     *
     * @param array $items
     * @param int $time
     * @return bool
     */
    public function setMulti($items = [], $time = 0)
    {
        return $this->conn()->setMulti($items, $time);
    }

    /**
     * getMulti
     *
     * @param array $keys
     * @return array
     */
    public function getMulti($keys = [])
    {
        return $this->conn()->getMulti($keys);
    }

    /**
     * deleteMulti
     *
     * @param array $keys
     * @param int $time
     * @return bool
     */
    public function deleteMulti($keys = [], $time = 0)
    {
        return $this->conn()->deleteMulti($keys, $time);
    }

    /**
     * 增加元素的值
     *
     * @param string $key
     * @param int $offset
     * @return int
     */
    public function increment($key = '', $offset = 1)
    {
        return $this->conn()->increment($key, $offset);
    }

    /**
     * 减小元素的值
     *
     * @param string $key
     * @param int $offset
     * @return int
     */
    public function decrement($key = '', $offset = 1)
    {
        return $this->conn()->decrement($key, $offset);
    }

    /**
     * 增加元素
     *
     * @param string $key
     * @param string $value
     * @param int $time
     * @return bool
     */
    public function add($key = '', $value = '', $time = 0)
    {
        return $this->conn()->add($key, $value, (0 == $time) ? 0 : time() + $time);
    }

    /**
     * 替换元素
     *
     * @param string $key
     * @param string $value
     * @param int $time
     * @return bool
     */
    public function replace($key = '', $value = '', $time = 0)
    {
        return $this->conn()->replace($key, $value, (0 == $time) ? 0 : time() + $time);
    }

}
