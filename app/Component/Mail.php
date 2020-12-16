<?php

declare(strict_types=1);
/**
 *
 * This file is part of the My App.
 *
 * Copyright CodingHePing 2016-2020.
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/codingheping/hyperf-chat-upgrade
 */
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

    public function check(string $type, string $email, string $code, ?RedisProxy $redis = null): bool
    {
        if (is_null($redis)) {
            $redis = $this->redis();
        }
        $smsCode = $redis->get($this->getKey($type, $email));
        if (! $smsCode) {
            return false;
        }

        return $code === $smsCode;
    }

    /**
     * @throws \Exception
     */
    public function send(string $type, string $title, string $email): bool
    {
        $key = $this->getKey($type, $email);

        if (! $smsCode = $this->getCode($key)) {
            $smsCode = random_int(100000, 999999);
        }

        $this->setCode($key, (string) $smsCode);
        try {

            $view = $this->view(config('view.engine'), 'emails.verify-code', ['service_name' => $title, 'sms_code' => $smsCode, 'domain' => config('config.domain.web_url')]);
            return $this->mail($email, $title, $view);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
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
     * 设置验证码缓存.
     *
     * @param string $key 缓存key
     * @param string $code 验证码
     * @param float|int $exp 过期时间
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
     * 删除验证码缓存.
     *
     * @param string $type 类型
     * @param string $email 邮箱地址
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

    /**
     * 获取缓存key.
     */
    private function getKey(string $type, string $mobile): string
    {
        return "email_code:{$type}:{$mobile}";
    }

    private function redis(): ?RedisProxy
    {
        return di(RedisFactory::class)->get(env('CLOUD_REDIS'));
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function mail(string $address, string $subject, string $view): bool
    {
        $config = config('mail');
        $mail = new PHPMailer(); //PHPMailer对象
        $mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP(); // 设定使用SMTP服务
        $mail->SMTPDebug = 0; // 关闭SMTP调试功能
        $mail->SMTPAuth = true; // 启用 SMTP 验证功能
        $mail->SMTPSecure = 'ssl'; // 使用安全协议
        $mail->Host = $config['host']; // SMTP 服务器
        $mail->Port = $config['port']; // SMTP服务器的端口号
        $mail->Username = $config['username']; // SMTP; // SMTP服务器用户名
        $mail->Password = $config['password']; // SMTP服务器密码
        $mail->SetFrom($config['from'], $config['name']); // 邮箱，昵称
        $mail->Subject = $subject;
        $mail->MsgHTML($view);
        $mail->AddAddress($address); // 收件人
        return $mail->Send();
    }

    /**
     * @param $template
     * @param array $params
     */
    private function view(string $engine, $template, $params = []): string
    {
        $config = config('view.config', []);
        return di()->get($engine)->render($template, $params, $config);
    }
}
