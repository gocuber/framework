<?php

/**
 * FileCache
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\FileCache;

class FileCache
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
    private $connect;

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * 切换连接
     *
     * @return $this
     */
    public function connect($key = null)
    {
        if (null === $key) {
            $this->connect = array_get($this->config, 'default', 'default');
        } else {
            $this->connect = $key;
        }

        return $this;
    }

    /**
     * 返回缓存文件全路径
     *
     * @param string $key
     * @return string
     */
    public function getFilePath($key = null)
    {
        if (!isset($key) or !array_get($this->config, 'connects.' . $this->connect)) {
            return false;
        }

        $config = array_get($this->config, 'connects.' . $this->connect);
        $md5    = md5($key);
        $dir    = $config['dir'];
        $subdir = array_get($config, 'subdir', 1) ? substr($md5, 0, 2) . '/' . substr($md5, 2, 2) . '/' . substr($md5, 4, 2) . '/' : '';
        $file   = $dir . $subdir . $key;
        return $file;
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

        $file = $this->getFilePath($key);
        if (!is_file($file)) {
            return $default;
        }

        if (false === ($data = file_get_contents($file))) {
            return $default;
        }

        $data = unserialize($data);
        $time = $data['time'];
        $data = $data['data'];
        return (0 === $time or $time >= time()) ? $data : $default;
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

        $file = $this->getFilePath($key);
        if (!mk_dir(dirname($file))) {
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
    public function delete($key = '')
    {
        if (empty($key)) {
            return false;
        }

        $file = $this->getFilePath($key);
        if (is_file($file)) {
            return @unlink($file);
        }
        return true;
    }

    /**
     * mGet
     *
     * @param array $keys
     * @return array|null
     */
    public function mGet($keys = [])
    {
        if (empty($keys) or !is_array($keys)) {
            return null;
        }

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $this->get($key);
        }
        return $data;
    }

    /**
     * mSet
     *
     * @param array $items
     * @param int $time
     * @return bool
     */
    public function mSet($items = [], $time = 0)
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
     * mDelete
     *
     * @param array $keys
     * @return array
     */
    public function mDelete($keys = [])
    {
        if (empty($keys) or !is_array($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

}
