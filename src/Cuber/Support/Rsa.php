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

    private static $_instance = null;

    private $public_key = '';

    private $private_key = '';

    private $public_id = '';

    private $private_id = '';

    private function __construct($key = null)
    {
        $rsa_dir = Config::get('rsa_dir');
        $this->public_key  = file_get_contents($rsa_dir . $key . '.public');
        $this->private_key = file_get_contents($rsa_dir . $key . '.private');
        $this->public_id   = openssl_pkey_get_public($this->public_key);
        $this->private_id  = openssl_pkey_get_private($this->private_key);
    }

    public static function getInstance($key = null)
    {
        if (!isset(self::$_instance[$key])) {
            self::$_instance[$key] = new self($key);
        }

        return self::$_instance[$key];
    }

    /**
     * 公匙加密
     *
     * @param string $data
     *
     * @return string
     */
    public function publicEncrypt($data = null)
    {
        openssl_public_encrypt($data, $encrypt, $this->public_id);
        return base64_encode($encrypt);
    }

    /**
     * 私匙加密
     *
     * @param string $data
     *
     * @return string
     */
    public function privateEncrypt($data = null)
    {
        openssl_private_encrypt($data, $encrypt, $this->private_id);
        return base64_encode($encrypt);
    }

    /**
     * 公匙解密
     *
     * @param string $data
     *
     * @return string
     */
    public function publicDecrypt($data = null)
    {
        openssl_public_decrypt(base64_decode($data), $decrypt, $this->public_id);
        return $decrypt;
    }

    /**
     * 私匙解密
     *
     * @param string $data
     *
     * @return string
     */
    public function privateDecrypt($data = null)
    {
        openssl_private_decrypt(base64_decode($data), $decrypt, $this->private_id);
        return $decrypt;
    }

    /**
     * publicKey
     *
     * @return string
     */
    public function publicKey()
    {
        return $this->public_key;
    }

    /**
     * privateKey
     *
     * @return string
     */
    public function privateKey()
    {
        return $this->private_key;
    }

    /**
     * 生成一对公私密钥
     *
     * @return array
     */
    public static function createKey()
    {
        $res = openssl_pkey_new(['private_key_bits' => 1024]);
        openssl_pkey_export($res, $private);
        $public = openssl_pkey_get_details($res);
        return ['public'=>$public['key'], 'private'=>$private];
    }

}
