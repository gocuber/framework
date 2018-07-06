<?php

/**
 * Curl
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Support;

class Curl
{

    /**
     * GET请求
     *
     * @param str $url
     * @param array $data
     * @param array $opt
     *
     * @return ['code'=>1, 'msg'=>'success', 'data'=>[]]
     */
    public static function getRequest($url = '', $data = [], $opt = [])
    {
        return self::request('get', $url, $data, $opt);
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
    public static function postRequest($url = '', $data = [], $opt = [])
    {
        return self::request('post', $url, $data, $opt);
    }

    /**
     * request
     *
     * @param str $type get|post
     * @param str $url
     * @param array|str $data
     * @param array $opt
     *
     * @return ['code'=>1, 'msg'=>'success', 'data'=>[]]
     */
    public static function request($type = 'get', $url = '', $data = [], $opt = [])
    {
        if (empty($type) or !in_array($type, ['get', 'post']) or empty($url)) {
        	return ['code'=>-1, 'msg'=>'error', 'data'=>[]];
        }

        if ('get' == $type and !empty($data) and is_array($data)) {

            $urls  = parse_url($url);
            $query = http_build_query($data);

            if (isset($urls['query'])) {
                $urls['query'] .= '&' . $query;
            } else {
                $urls['query'] = $query;
            }

            $url = self::url($urls);

        }

        $header = empty($opt['header']) ? [] : $opt['header'];

        if (!empty($header) and !is_array($header)) {
            $tmp = explode("\n", $header);
            $header = [];
            foreach ($tmp as $value) {
                $t = explode(':', strtr($value, ["\r"=>'']));
                $header[$t[0]] = array_get($t, 1, '');
            }
            unset($tmp);
        }

        if (!empty($opt['ip'])) {
            $host = parse_url($url, PHP_URL_HOST);
            $url  = strtr($url, [$host => $opt['ip']]);
            $header['Host'] = $host;
        }

        if (!empty($opt['cookie'])) {
            $header['Cookie'] = $opt['cookie'];
        }

        if (!empty($header)) {
            $tmp = [];
            foreach ($header as $key=>$value) {
                $tmp[] = $key . ': ' . $value;
            }
            $header = $tmp;
            unset($tmp);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, array_get($opt, 'connecttimeout', 10));
        curl_setopt($ch, CURLOPT_TIMEOUT, array_get($opt, 'timeout', 30));
        curl_setopt($ch, CURLOPT_USERAGENT, array_get($opt, 'useragent', ''));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        !empty($header) and curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if ('post' == $type) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $result = curl_exec($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);

        $code = (false === $result) ? 0 : 1;
        $msg  = (false === $result) ? 'error' : 'success';

        return ['code'=>$code, 'msg'=>$msg, 'data'=>['response'=>$result, 'info'=>$info]];
    }

    /**
     * url
     *
     * @param array $urls
     *
     * @param str $url
     */
    private static function url($urls = [])
    {
        if (empty($urls) or !is_array($urls)) {
            return '';
        }

        $url = '';

        isset($urls['scheme'])   and $url .= $urls['scheme'];
        isset($urls['host'])     and $url .= '://' . $urls['host'];
        isset($urls['port'])     and $url .= ':' . $urls['port'];
        isset($urls['path'])     and $url .= $urls['path'];
        isset($urls['query'])    and $url .= '?' . $urls['query'];
        isset($urls['fragment']) and $url .= '#' . $urls['fragment'];

        return $url;
    }

}
