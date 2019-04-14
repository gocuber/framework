<?php

/**
 * Memcache
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

class Memcache
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
        return $mem->set($key, $value, 0 ,$time);
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
        if(empty($items) or !is_array($items)){
            return false;
        }

        foreach($items as $key=>$value){
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
    public function getMulti($keys = array())
    {
        $mem = $this->conn();
        return $mem->get($keys);
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
        if(empty($keys) or !is_array($keys)){
            return false;
        }

        foreach($keys as $key){
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
        return $mem->add($key, $value, 0, time() + $time);
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
        return $mem->replace($key, $value, 0, time() + $time);
    }

    /**
     * 关闭memcache连接
     *
     * @return bool
     */
    public function close()
    {
        if (isset($this->conn)) {
            $this->conn->close();
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

            $mem = new Memcache();
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
            $this->conn = $mem;
        }

        return $this->conn;
    }

}
