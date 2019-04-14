<?php

/**
 * Memcached
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

class Memcached
{

    private static $instance;

    private $config;

    private $conn;

    private function __construct($config = null)
    {
        $this->config = $config;
    }

    public static function connect($key = 'default')
    {
        $conf = Config::mem($key);

        $key = md5(serialize($conf));
        if (!isset(self::$instance[$key])) {
            self::$instance[$key] = new self($conf);
        }

        return self::$instance[$key];
    }

    /**
     * set
     *
     * @param string $key
     * @param string $value
     * @param int $time
     * @return bool
     */
    public function set($key = '', $value = '', $time = 3600)
    {
        $mem = $this->conn();
        return $mem->set($key, $value, time() + $time);
    }

    /**
     * get
     *
     * @param string $key
     * @return string
     */
    public function get($key = '')
    {
        $mem = $this->conn();
        return $mem->get($key);
    }

    /**
     * del
     *
     * @param string $key
     * @param int $time
     * @return bool
     */
    public function del($key = '', $time = 0)
    {
        $mem = $this->conn();
        return $mem->delete($key, $time);
    }

    /**
     * setMulti
     *
     * @param array $items
     * @param int $time
     * @return bool
     */
    public function setMulti($items = array(), $time = 3600)
    {
        $mem = $this->conn();
        return $mem->setMulti($items, time() + $time);
    }

    /**
     * getMulti
     *
     * @param array $keys
     * @return array
     */
    public function getMulti($keys = array())
    {
        $mem = $this->conn();
        return $mem->getMulti($keys);
    }

    /**
     * delMulti
     *
     * @param array $keys
     * @param int $time
     * @return bool
     */
    public function delMulti($keys = array(), $time = 0)
    {
        $mem = $this->conn();
        return $mem->deleteMulti($keys, $time);
    }

    /**
     * 增加元素的值
     *
     * @param string $key
     * @param int $offset
     * @return int
     */
    public function inc($key = '', $offset = 1)
    {
        $mem = $this->conn();
        return $mem->increment($key, $offset);
    }

    /**
     * 减小元素的值
     *
     * @param string $key
     * @param int $offset
     * @return int
     */
    public function dec($key = '', $offset = 1)
    {
        $mem = $this->conn();
        return $mem->decrement($key, $offset);
    }

    /**
     * 增加元素
     *
     * @param string $key
     * @param string $value
     * @param int $time
     * @return bool
     */
    public function add($key = '', $value = '', $time = 3600)
    {
        $mem = $this->conn();
        return $mem->add($key, $value, $_SERVER['REQUEST_TIME'] + $time);
    }

    /**
     * 替换元素
     *
     * @param string $key
     * @param string $value
     * @param int $time
     * @return bool
     */
    public function replace($key = '', $value = '', $time = 3600)
    {
        $mem = $this->conn();
        return $mem->replace($key, $value, $_SERVER['REQUEST_TIME'] + $time);
    }

    /**
     * 关闭memcache连接
     *
     * @return bool
     */
    public function close()
    {
        if (isset($this->conn)) {
            $this->conn->quit();
            $this->conn = null;
        }

        return true;
    }

    /**
     * conn
     *
     * @return mem
     */
    private function conn()
    {
        if (!isset($this->conn)) {
            $config = $this->config;

            $key = md5(serialize($config));
            $mem = new Memcached($key);
            if (isset($config[0]) and is_array($config[0])) {
                $conf = [];
                foreach ($config as $value) {
                    $conf[] = array_values($value);
                }
                $mem->addServers($conf);
            } else {
                $mem->addServer($config['host'], $config['port']);
            }
            $this->conn = $mem;
        }

        return $this->conn;
    }

}
