<?php
declare(strict_types = 1);

namespace App\Component;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    public const FORGET_PASSWORD = 'forget_password';
    public const CHANGE_MOBILE = 'change_mobile';
    public const CHANGE_REGISTER = 'user_register';
    public const CHANGE_EMAIL = 'change_email';

    /**
     * 获取缓存key
     *
     * @param string $type
     * @param string $mobile
     *
     * @return string
     */
    private function getKey(string $type, string $mobile) : string
    {
        return "email_code:{$type}:{$mobile}";
    }

    /**
     * @param string          $type
     * @param string          $email
     * @param string          $code
     * @param null|RedisProxy $redis
     *
     * @return bool
     */
    public function check(string $type, string $email, string $code, ?RedisProxy $redis = null) : bool
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }
        $smsCode = $redis->get($this->getKey($type, $email));
        if (!$smsCode) {
            return false;
        }

        return $code === $smsCode;
    }

    /**
     * @return null|\Hyperf\Redis\RedisProxy
     */
    private function redis() : ?RedisProxy
    {
        return di(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }

    /**
     * @param string $type
     * @param string $title
     * @param string $email
     *
     * @return bool
     * @throws \Exception
     */
    public function send(string $type, string $title, string $email) : bool
    {
        $key = $this->getKey($type, $email);
        if (!$sms_code = $this->getCode($key)) {
            $sms_code = random_int(100000, 999999);
        }

        $this->setCode($key, $sms_code);
        try {
            $view = $this->view(config('view.engine'), 'emails.verify-code', ['service_name' => $title, 'sms_code' => $sms_code, 'domain' => config('config.domain.web_url')]);
            return $this->mail($email, $title, $view);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string          $key
     * @param null|RedisProxy $redis
     *
     * @return mixed
     */
    public function getCode(string $key, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }
        return $redis->get($key);
    }

    /**
     * 设置验证码缓存
     *
     * @param string                        $key  缓存key
     * @param string                        $code 验证码
     * @param float|int                     $exp  过期时间
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return mixed
     */
    public function setCode(string $key, string $code, $exp = 60 * 15, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }
        return $redis->setex($key, $exp, $code);
    }

    /**
     * @param string $address
     * @param string $subject
     * @param string $view
     *
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function mail(string $address, string $subject, string $view) : bool
    {
        $mail          = new PHPMailer(); //PHPMailer对象
        $mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP(); // 设定使用SMTP服务
        $mail->SMTPDebug  = 0; // 关闭SMTP调试功能
        $mail->SMTPAuth   = true; // 启用 SMTP 验证功能
        $mail->SMTPSecure = 'ssl'; // 使用安全协议
        $mail->Host       = 'smtp.163.com'; // SMTP 服务器
        $mail->Port       = '994'; // SMTP服务器的端口号
        $mail->Username   = ''; // SMTP服务器用户名
        $mail->Password   = ''; // SMTP服务器密码
        $mail->SetFrom('', ''); // 邮箱，昵称
        $mail->Subject = $subject;
        $mail->MsgHTML($view);
        $mail->AddAddress($address); // 收件人
        return $mail->Send();
    }

    /**
     * @param string                              $engine
     * @param                                     $template
     * @param array                               $params
     *
     * @return string
     */
    private function view(string $engine, $template, $params = []) : string
    {
        $config = config('view.config', []);
        return di()->get($engine)->render($template, $params, $config);
    }

    /**
     * 删除验证码缓存
     *
     * @param string                        $type  类型
     * @param string                        $email 邮箱地址
     * @param null|\Hyperf\Redis\RedisProxy $redis
     *
     * @return mixed
     */
    public function delCode(string $type, string $email, ?RedisProxy $redis = null)
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }
        return $redis->del($this->getKey($type, $email));
    }
}
