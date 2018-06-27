<?php

/**
 * Mail
 *
 * @author Cuber <dafei.net@gmail.com>
 */
namespace Cuber\Support;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{

    /**
     * send
     *
     * @param string $server      Server
     *
     * @param string $email       收件人email
     * @param string $title       标题
     * @param string $content     内容
     * @param string $attachment  附件
     *
     * @return ['code'=>1, 'msg'=>'success']
     */
    public static function send($param = array())
    {
        if(empty($param['email']) or empty($param['title']) or empty($param['content'])){
            return array('code'=>-1, 'msg'=>'参数错误');
        }

        $server = empty($param['server']) ? 'default' : $param['server'];
        if(empty($GLOBALS['_G']['email'][$server]) or !is_array($GLOBALS['_G']['email'][$server])){
            return array('code'=>-2, 'msg'=>'邮件配置错误');
        }
        $server = $GLOBALS['_G']['email'][$server];

        $dir = CUBE_DIR . 'lib/PHPMailer/';

        require_once($dir . 'src/Exception.php');
        require_once($dir . 'src/PHPMailer.php');
        require_once($dir . 'src/SMTP.php');

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $server['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $server['username'];
            $mail->Password   = $server['password'];
            $mail->SMTPSecure = $server['smtpsecure'];
            $mail->Port       = $server['port'];

            // Recipients
            $mail->setFrom($server['from']);

            $emails = explode(',', $param['email']);
            foreach($emails as $email){
                $mail->addAddress($email);
            }

            // Attachments
            if(!empty($param['attachment'])){
                $attachments = explode(',', $param['attachment']);
                foreach($attachments as $attachment){
                    $mail->addAttachment($attachment);
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $param['title'];
            $mail->Body    = $param['content'];

            $ret = $mail->send();
            return array('code'=>1, 'msg'=>'success', 'data'=>$ret);
        } catch (Exception $e) {
            return array('code'=>-1, 'msg'=>$mail->ErrorInfo);
        }
    }

}
