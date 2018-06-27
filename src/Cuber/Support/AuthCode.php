<?php

/**
 * Util_AuthCode
 *
 * 验证码生成类
 */
defined('IN_CUBE') or exit();

class Util_AuthCode
{

    /**
     * 字母+数字的验证码生成
     *
     * @return img
     */
    public static function showCode()
    {

        //1.创建黑色画布
        $image = imagecreatetruecolor(100, 30);

        //2.为画布定义(背景)颜色
        $bgcolor = imagecolorallocate($image, 255, 255, 255);

        //3.填充颜色
        imagefill($image, 0, 0, $bgcolor);

        // 4.设置验证码内容

        //4.1 定义验证码的内容
        //$content = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $content = "ABCDEFGHIJKLMNPQRSTUVWXYZ12345678923456789";

        //4.1 创建一个变量存储产生的验证码数据，便于用户提交核对
        $captcha = "";
        for ($i = 0; $i < 4; $i++) {
            // 字体大小
            $fontsize = 12;
            // 字体颜色
            $fontcolor = imagecolorallocate($image, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
            // 设置字体内容
            $fontcontent = substr($content, mt_rand(0, strlen($content)-1), 1);
            $captcha .= $fontcontent;
            // 显示的坐标
            $x = ($i * 100 / 4) + mt_rand(5, 10);
            $y = mt_rand(5, 10);
            // 填充内容到画布中
            imagestring($image, $fontsize, $x, $y, $fontcontent, $fontcolor);
        }
        self::setCode($captcha);

        //4.3 设置背景干扰元素
        for ($i = 0; $i < 200; $i++) {
            $pointcolor = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
            imagesetpixel($image, mt_rand(1, 99), mt_rand(1, 29), $pointcolor);
        }

        //4.4 设置干扰线
        for ($i = 0; $i < 2; $i++) {
            $linecolor = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
            imageline($image, mt_rand(1, 99), mt_rand(1, 29), mt_rand(1, 99), mt_rand(1, 29), $linecolor);
        }

        //5.向浏览器输出图片头信息
        //6.输出图片到浏览器
        if (function_exists('imagepng')) {
            header('content-type:image/png');
            imagepng($image);
        } elseif (function_exists('imagejpeg')) {
            header('Content-type: image/jpeg');
            imagejpeg($image);
        } elseif (function_exists('imagegif')) {
            header('Content-type: image/gif');
            imagegif($image);
        } elseif (function_exists('imagewbmp')) {
        	header('Content-type: image/vnd.wap.wbmp');
            imagewbmp($image);
        }

        //7.销毁图片
        imagedestroy($image);
    }

    /**
     * key
     *
     * @return string
     */
    private static function key()
    {
    	return 'RyYkOr11MrQll1100OOOO00OOO00O0O0OO1';
    }

    /**
     * setCode
     *
     * @param string $code
     * @return bool
     */
    public static function setCode($code = '')
    {
        return Session::set(self::key(), $code);
    }

    /**
     * getCode
     *
     * @return string
     */
    public static function getCode()
    {
        return Session::get(self::key());
    }

}
