<?php

declare(strict_types = 1);
/**
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/Hyperf-Glory/socket-io
 */
namespace App\Service;

use App\Component\Hash;
use App\Model\ArticleClass;
use App\Model\GroupMember;
use App\Model\User;
use App\Model\UsersChatList;
use App\Model\UsersFriend;
use App\Model\UsersFriendsApply;
use Exception;
use Hyperf\DbConnection\Db;

class UserService
{
    /**
     * 账号注册.
     *
     * @param string $mobile
     * @param string $password
     * @param string $nickname
     *
     * @return bool
     */
    public function register(string $mobile, string $password, string $nickname) : bool
    {
        try {
            Db::beginTransaction();
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
                'created_at' => time(),
            ]);
            Db::commit();
        } catch (Exception $e) {
            $result = false;
            Db::rollBack();
        }
        return $result;
    }

    /**
     * 重制密码
     *
     * @param string $mobile
     * @param string $password
     *
     * @return bool
     */
    public function resetPassword(string $mobile, string $password) : bool
    {
        return (bool)User::query()->where('mobile', $mobile)->update([
            'password' => Hash::make($password),
        ]);
    }

    /**
     * 修改绑定的手机号.
     */
    public function changeMobile(int $uid, string $mobile) : array
    {
        $selectUid = User::query()->where('mobile', $mobile)->value('id');
        if ($selectUid) {
            return [false, '手机号已被他人绑定'];
        }

        $bool = User::query()->where('id', $uid)->update([
            'mobile' => $mobile,
        ]);
        return [(bool)$bool, null];
    }

    public function sendEmailCode(string $email) : void
    {
        //TODO 发送邮件
    }

    /**
     * 获取用户所有的群聊ID.
     *
     * @param int $uid
     *
     * @return array
     */
    public function getUserGroupIds(int $uid) : array
    {
        return GroupMember::query()->where('user_id', $uid)->where('status', 0)->get()->pluck('group_id')->toarray();
    }

    /**
     * @return null|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object
     */
    public function get(int $uid)
    {
        return User::query()->where('id', $uid)->first();
    }

    /**
     * 验证用户密码是否正确.
     *
     * @param string $input    用户输入密码
     * @param string $password 账户密码
     *
     * @return bool
     */
    public function checkPassword(string $input, string $password) : bool
    {
        return Hash::verify($input, $password);
    }

    /**
     * 获取用户信息.
     *
     * @param int   $uid
     * @param array $field 查询字段
     *
     * @return User
     */
    public function findById(int $uid, $field = ['*']) : User
    {
        return User::where('id', $uid)->first($field);
    }

    /**
     * 获取用户所在的群聊.
     *
     * @param int $uid 用户ID
     *
     * @return array
     */
    public function getUserChatGroups(int $uid) : array
    {
        $items = GroupMember::select(['User_group.id', 'User_group.group_name', 'User_group.avatar', 'User_group.group_profile', 'User_group.user_id as group_user_id'])
                                ->join('User_group', 'User_group.id', '=', 'User_group_member.group_id')
                                ->where([
                                    ['User_group_member.user_id', '=', $uid],
                                    ['User_group_member.status', '=', 0],
                                ])
                                ->orderBy('id', 'desc')->get()->toarray();

        foreach ($items as $key => $item) {
            // 判断当前用户是否是群主
            $items[$key]['isGroupLeader'] = $item['group_user_id'] === $uid;

            //删除无关字段
            unset($items[$key]['group_user_id']);

            // 是否消息免打扰
            $items[$key]['not_disturb'] = UsersChatList::where([
                ['uid', '=', $uid],
                ['type', '=', 2],
                ['group_id', '=', $item['id']],
            ])->value('not_disturb');
        }

        return $items;
    }

    /**
     * 通过手机号查找用户.
     *
     * @param array $where   查询条件
     * @param int   $user_id 当前登录用户的ID
     */
    public function searchUserInfo(array $where, int $user_id) : array
    {
        $info = User::select(['id', 'mobile', 'nickname', 'avatar', 'gender', 'motto']);
        if (isset($where['uid'])) {
            $info->where(['id' => $where['uid']]);
        }

        if (isset($where['mobile'])) {
            $info->where(['mobile' => $where['mobile']]);
        }
        $info = $info->first();
        $info = $info ? $info->toArray() : [];
        if ($info) {
            $info['friend_status']   = 0; //朋友关系状态  0:本人  1:陌生人 2:朋友
            $info['nickname_remark'] = '';
            $info['friend_apply']    = 0;

            // 判断查询信息是否是自己
            if ($info['id'] !== $user_id) {
                $friend_id = $info['id'];
                /**
                 * @var UserFriends $friendInfo
                 */
                $friendInfo = UsersFriend::select(['id', 'user1', 'user2', 'active', 'user1_remark', 'user2_remark'])->where(function ($query) use ($friend_id, $user_id)
                {
                    $query->where('user1', '=', $user_id)->where('user2', '=', $friend_id)->where('status', 1);
                })->orWhere(function ($query) use ($friend_id, $user_id)
                {
                    $query->where('user1', '=', $friend_id)->where('user2', '=', $user_id)->where('status', 1);
                })->first();

                $info['friend_status'] = $friendInfo ? 2 : 1;
                if ($friendInfo) {
                    $info['nickname_remark'] = ($friendInfo->user1 === $friend_id) ? $friendInfo->user2_remark : $friendInfo->user1_remark;
                } else {
                    $res                  = UsersFriendsApply::where('user_id', $user_id)->where('friend_id', $info['id'])->where('status', 0)->orderBy('id', 'desc')->exists();
                    $info['friend_apply'] = $res ? 1 : 0;
                }
            }
        }

        return $info;
    }
}
