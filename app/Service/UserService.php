<?php
declare(strict_types = 1);

namespace App\Service;

use App\Component\Hash;
use App\Model\ArticleClass;
use App\Model\User;
use App\Model\UsersGroupMember;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use PHPMailer\PHPMailer\PHPMailer;

class UserService
{

    /**
     * 账号注册
     *
     * @param string $mobile
     * @param string $password
     * @param string $nickname
     *
     * @return bool
     */
    public function register(string $mobile, string $password, string $nickname)
    {
        try {
            $user             = new User();
            $user->nickname   = $nickname;
            $user->mobile     = $mobile;
            $user->password   = Hash::make($password);
            $user->created_at = date('Y-m-d H:i:s');
            $result           = $user->save();

            //创建用户的默认笔记分类
            ArticleClass::query()->insert([
                'user_id'    => $user->id,
                'class_name' => '我的笔记',
                'is_default' => 1,
                'sort'       => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $result = false;
            Db::rollBack();;
        }
        return $result ? true : false;
    }

    /**
     * 重制密码
     *
     * @param string $mobile
     * @param string $password
     *
     * @return int
     * @throws \Crypto\HashException
     */
    public function resetPassword(string $mobile, string $password)
    {
        return User::query()->where('mobile', $mobile)->update([
            'password' => Hash::make($password)
        ]);
    }

    /**
     * 修改绑定的手机号
     *
     * @param int    $uid
     * @param string $mobile
     *
     * @return array
     */
    public function changeMobile(int $uid, string $mobile)
    {
        $uid = User::query()->where('mobile', $mobile)->value('id');
        if ($uid) {
            return [false, '手机号已被他人绑定'];
        }

        $bool = User::query()->where('uid', $uid)->update([
            'mobile' => $mobile
        ]);
        return [(bool)$bool, null];
    }

    public function sendEmailCode(string $email)
    {
        $key      = "email_code:{$email}";
        $sms_code = mt_rand(100000, 999999);
        $mail     = make(PHPMailer::class);
        //TODO 发送邮件
    }

    /**
     * 获取用户所有的群聊ID
     * @param int $uid
     *
     * @return array
     */
    public function getUserGroupIds(int $uid)
    {
        return UsersGroupMember::query()->where('user_id', $uid)->where('status', 0)->get()->pluck('group_id')->toarray();
    }

}
