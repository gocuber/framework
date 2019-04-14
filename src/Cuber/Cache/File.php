<?php

/**
 * File
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Cache;

use Cuber\Support\Facades\Config;

class File
{

    private static $instance;
    private $config;

    private function __construct($config = null)
    {
        $this->config = $config;
    }

    public static function connect($key = 'default')
    {
        $conf = Config::fc($key);

        $key = md5(serialize($conf));
        if (!isset(self::$instance[$key])) {
            self::$instance[$key] = new self($conf);
        }

        return self::$instance[$key];
    }

    /**
     * 获取
     *
     * @param string $key
     * @param string $default
     * @return $data
     */
    public function get($key = '', $default = null)
    {
        if (empty($key)) {
            return $default;
        }

        $file = $this->getFile($key);
        if (!is_file($file)) {
            return $default;
        }

        if (false === ($data = file_get_contents($file))) {
            return $default;
        }

        $data = unserialize($data);
        $time = $data['time'];
        $data = $data['data'];
        return (0 === $time or $time > time()) ? $data : $default;
    }

    /**
     * 写入
     *
     * @param string $key
     * @param string $value
     * @param int $time
     *
     * @return bool
     */
    public function set($key = '', $value = '', $time = 0)
    {
        if (empty($key)) {
            return false;
        }

        $file = $this->getFile($key);
        if (!\mk_dir(dirname($file))) {
            return false;
        }

        $data = [];
        $data['time'] = (0 == $time) ? 0 : time() + $time;
        $data['data'] = $value;
        $data = serialize($data);
        if (function_exists('file_put_contents')) {
            file_put_contents($file, $data);
        } else {
            $handle = fopen($file, 'wb');
            fwrite($handle, $data);
            fclose($handle);
        }
        return true;
    }

    /**
     * 删除
     *
     * @param string $key
     * @return bool
     */
    public function del($key = '')
    {
        if (empty($key)) {
            return false;
        }

        $file = $this->getFile($key);
        if (is_file($file)) {
            return @unlink($file);
        }
        return true;
    }

    /**
     * getMulti
     *
     * @param array $keys
     * @return array
     */
    public function getMulti($keys = [], $default = null)
    {
        if (empty($keys) or !is_array($keys)) {
            return false;
        }

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $this->get($key, $default);
        }
        return $data;
    }

    /**
     * setMulti
     *
     * @param array $items
     * @param int $time
     * @return bool
     */
    public function setMulti($items = [], $time = 3600)
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
     * delMulti
     *
     * @param array $keys
     * @return array
     */
    public function delMulti($keys = [])
    {
        if (empty($keys) or !is_array($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            $this->del($key);
        }
        return true;
    }

    /**
     * 返回缓存文件全路径
     *
     * @param string $key
     * @return string
     */
    public function getFile($key = false)
    {
        if (!isset($key)) {
            return false;
        }

        $md5    = md5($key);
        $dir    = $this->config['dir'];
        $subdir = $this->config['is_subdir'] ? substr($md5,0,2).'/'.substr($md5,2,2).'/'.substr($md5,4,2).'/' : '';
        $file   = $dir . $subdir . $key;
        return $file;
    }

}
