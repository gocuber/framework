<?php

/**
 * Rsa
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Support;

use Cuber\Config\Config;

class Rsa
{

    private $_private_key = '';

    private $_public_key = '';

    private static $_instance = null;

    private function __construct($key = null)
    {
        isset($key['public']) and $this->_public_key = $key['public'];
        isset($key['private']) and $this->_private_key = $key['private'];
    }

    public static function getInstance($key = array())
    {
        if(empty($key)){
            $key = Config::get('rsa');
        }

        $_key = md5(serialize($key));
        if(!isset(self::$_instance[$_key])){
            self::$_instance[$_key] = new self($key);
        }
        return self::$_instance[$_key];
    }

    /**
     * 加密
     *
     * @param string $data
     * @param string $key   private私匙 public公匙
     *
     * @return string
     */
    public function encrypt($data = null, $key = 'public')
    {
        return ('private'==$key) ? $this->privateEncrypt($data) : $this->publicEncrypt($data);
    }

    /**
     * 解密
     *
     * @param string $data
     * @param string $key   private私匙 public公匙
     *
     * @return string
     */
    public function decrypt($data = null, $key = 'private', $js = false)
    {
        return ('public'==$key) ? $this->publicDecrypt($data) : $this->privateDecrypt($data, $js);
    }

    /**
     * 私匙加密
     *
     * @param string $data
     *
     * @return string
     */
    private function privateEncrypt($data = null)
    {
        openssl_private_encrypt($data, $encrypted, $this->privateKey());
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }

    /**
     * 公匙加密
     *
     * @param string $data
     *
     * @return string
     */
    private function publicEncrypt($data = null)
    {
        openssl_public_encrypt($data, $encrypted, $this->publicKey());
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }

    /**
     * 私匙解密
     *
     * @param string $data
     *
     * @return string
     */
    private function privateDecrypt($data = null, $js = false)
    {
        $padding = $js ? OPENSSL_NO_PADDING : OPENSSL_PKCS1_PADDING;
        $js and $data = base64_encode(@pack("H*",trim($data)));
        if(openssl_private_decrypt(base64_decode($data), $decrypted, $this->privateKey(), $padding)){
            $decrypted = $js ? rtrim(strrev($decrypted), "/0") : "".$decrypted;
            return rtrim($decrypted);
        }
        return '';
    }

    /**
     * 公匙解密
     *
     * @param string $data
     *
     * @return string
     */
    private function publicDecrypt($data = null)
    {
        openssl_public_decrypt(base64_decode($data), $decrypted, $this->publicKey());
        return $decrypted;
    }

    /**
     * privateKey
     *
     * @return resource
     */
    private function privateKey()
    {
        return openssl_pkey_get_private($this->_private_key);
    }

    /**
     * publicKey
     *
     * @return resource
     */
    private function publicKey()
    {
    	return openssl_pkey_get_public($this->_public_key);
    }

    /**
     * getPrivateKey
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->_private_key;
    }

    /**
     * getPublicKey
     *
     * @return string
     */
    public function getPublicKey()
    {
    	return strtr($this->_public_key, array('-----BEGIN PUBLIC KEY-----'=>'','-----END PUBLIC KEY-----'=>'',"\n"=>'',"\r"=>''));
    }

    /**
     * 生成一对公私密钥
     *
     * @return array
     */
    public function createKey()
    {
        $res = openssl_pkey_new();
        openssl_pkey_export($res, $private);
        $public = openssl_pkey_get_details($res);
        return array('public'=>$public['key'], 'private'=>$private);
    }

}
