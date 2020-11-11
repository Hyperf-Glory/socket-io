<?php
declare(strict_types = 1);

namespace App\Service;

use App\Component\Hash;
use App\Model\ArticleClass;
use App\Model\Users;
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
            Db::beginTransaction();
            $user             = new Users();
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
                'created_at' => time()
            ]);
            Db::commit();
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
     */
    public function resetPassword(string $mobile, string $password)
    {
        return Users::query()->where('mobile', $mobile)->update([
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
        $uid = Users::query()->where('mobile', $mobile)->value('id');
        if ($uid) {
            return [false, '手机号已被他人绑定'];
        }

        $bool = Users::query()->where('uid', $uid)->update([
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
     *
     * @param int $uid
     *
     * @return array
     */
    public function getUserGroupIds(int $uid)
    {
        return UsersGroupMember::query()->where('user_id', $uid)->where('status', 0)->get()->pluck('group_id')->toarray();
    }

    /**
     * @param int $uid
     *
     * @return null|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|Users
     */
    public function get(int $uid)
    {
        return Users::query()->where('id', $uid)->first() ?? null;
    }

    /**
     * 验证用户密码是否正确
     *
     * @param string $input    用户输入密码
     * @param string $password 账户密码
     *
     * @return bool
     */
    public function checkPassword(string $input, string $password)
    {
        return Hash::verify($input, $password);
    }

}
