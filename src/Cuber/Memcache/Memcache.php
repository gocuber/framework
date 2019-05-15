<?php

/**
 * Memcache
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Memcache;

class Memcache
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

    public function __construct($config = null)
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
     * @return \Memcache
     */
    private function conn()
    {
        if (!isset($this->conn[$this->connect])) {
            $config = array_get($this->config, 'connects.' . $this->connect, []);
            $mem = new \Memcache();
            if (isset($config[0]) and is_array($config[0])) {
                foreach ($config as $value) {
                    if (isset($value['weight'])) {
                        $mem->addServer($value['host'], $value['port'], true, $value['weight']);
                    } else {
                        $mem->addServer($value['host'], $value['port'], true);
                    }
                }
            } else {
                $mem->addServer($config['host'], $config['port'], true);
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
            $this->conn[$this->connect]->close();
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
            $this->conn[$conn]->close();
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
        return $this->conn()->set($key, $value, 0, $time);
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
        if (empty($items) or !is_array($items)) {
            return false;
        }

        foreach ($items as $key=>$value) {
            $this->set($key, $value, $time);
        }
        return true;
    }

    /**
     * getMulti
     *
     * @param array $keys
     * @return array
     */
    public function getMulti($keys = [])
    {
        return $this->conn()->get($keys);
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
        if (empty($keys) or !is_array($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            $this->del($key, $time);
        }
        return true;
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
        return $this->conn()->add($key, $value, 0, $time);
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
        return $this->conn()->replace($key, $value, 0, $time);
    }

}
