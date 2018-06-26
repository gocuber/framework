<?php

/**
 * Util_Curl
 *
 * @author Cube <dafei.net@gmail.com>
 */
defined('IN_CUBE') or exit();

class Util_Curl
{

    /**
     * GET请求
     *
     * @param str $url
     * @param array $opt
     *
     * @return ['code'=>1, 'msg'=>'success', 'data'=>[]]
     */
    public static function getRequest($url = '', $opt = array())
    {
        return self::request('get', $url, null, $opt);
    }

    /**
     * POST请求
     *
     * @param str $url
     * @param array $data
     * @param array $opt
     *
     * @return ['code'=>1, 'msg'=>'success', 'data'=>[]]
     */
    public static function postRequest($url = '', $data = array(), $opt = array())
    {
        return self::request('post', $url, $data, $opt);
    }

    /**
     * request
     *
     * @param str $type get|post
     * @param str $url
     * @param array $data
     * @param array $opt
     *
     * @return ['code'=>1, 'msg'=>'success', 'data'=>[]]
     */
    public static function request($type = 'get', $url = '', $data = array(), $opt = array())
    {
        $header = array();

        if(!empty($opt['ip'])){
            $urlinfo  = parse_url($url);
            $host     = $urlinfo['host'];
            $url      = strtr($url, array($host => $opt['ip']));
            $header[] = 'HOST: ' . $host;
        }
        if(!empty($opt['cookie'])){
            $header[] = 'COOKIE: ' . $opt['cookie'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);     // 在尝试连接时等待的秒数。设置为0，则无限等待。
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);            // 允许 cURL 函数执行的最长秒数。
        curl_setopt($ch, CURLOPT_USERAGENT, 'useragent');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   // TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。

        // 设置 HTTP 头字段的数组。格式： array('Content-type: text/plain', 'Content-length: 100')
        !empty($header) and curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if('post' == $type){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // 全部数据使用HTTP协议中的 "POST" 操作来发送。
        }

        $result = curl_exec($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);

        $code = (false === $result) ? 0 : 1;
        $msg  = (false === $result) ? 'error' : 'success';
        $info['content'] = $result;
        return array('code'=>$code, 'msg'=>$msg, 'data'=>$info);
    }

}
