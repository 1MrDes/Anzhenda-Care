<?php
namespace vm\org;

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    protected $config;
    
    public function __construct(array $mailConfig)
    {
        $this->config = $mailConfig;
    }

    /**
     * 系统邮件发送函数
     *
     * @param string $to            接收邮件者邮箱
     * @param string $toName          接收邮件者名称
     * @param string $subject       邮件主题
     * @param string $body          邮件内容
     * @param string $attachment    附件列表
     * @throws
     * @return boolean
     */
    function send($to, $toName = '', $subject = '', $body = '', $attachment = null)
    {
        $mail = new PHPMailer (true); // PHPMailer对象
        try {
            $mail->CharSet = 'UTF-8'; // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码

            //$mail->AddAddress($address);//添加联系人

            $mail->IsSMTP(); // 设定使用SMTP服务
            $mail->SMTPDebug = 0; // 关闭SMTP调试功能
            // 1 = errors and messages
            // 2 = messages only
            $mail->SMTPAuth = true; // 启用 SMTP 验证功能
            $mail->SMTPSecure = 'ssl'; // 使用安全协议
            $mail->Host = $this->config['SMTP_HOST']; // SMTP 服务器
            $mail->Port = $this->config['SMTP_PORT']; // SMTP服务器的端口号
            $mail->Username = $this->config['SMTP_USER']; // SMTP服务器用户名
            $mail->Password = $this->config['SMTP_PASS']; // SMTP服务器密码
            $mail->SetFrom($this->config['FROM_EMAIL'], $this->config['FROM_NAME']);
            $replyEmail = $this->config['REPLY_EMAIL'] ? $this->config['REPLY_EMAIL'] : $this->config['FROM_EMAIL'];
            $replyName = $this->config['REPLY_NAME'] ? $this->config['REPLY_NAME'] : $this->config['FROM_NAME'];
            $mail->AddReplyTo($replyEmail, $replyName);
            $mail->Subject = $subject;

            $mail->MsgHTML($body);
            $mail->AddAddress($to, $toName);
            if (is_array($attachment)) { // 添加附件
                foreach ($attachment as $file) {
                    is_file($file) && $mail->AddAttachment($file);
                }
            }
            $mail->Send();
            return true;
        } catch (Exception $e) {
            throw new \think\Exception($mail->ErrorInfo);
        }
    }
}