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
namespace App\Service;

use App\Model\Users;
use App\Model\UsersFriends;
use App\Model\UsersFriendsApply;
use App\Service\Traits\PagingTrait;
use Exception;
use Hyperf\DbConnection\Db;
use RuntimeException;

class UserFriendService
{
    use PagingTrait;

    use PagingTrait;

    /**
     * 获取指定用户的所有朋友的用户ID.
     *
     * @param int $uid 指定用户ID
     *
     * @throws \Throwable
     * @return mixed
     */
    public function getFriends(int $uid)
    {
        try {
            $prefix = config('databases.default.prefix');
            $table = $prefix . '_users_friends';
            $sql = <<<'SQL'
SELECT user2 as uid
from im_users_friends
where user1 = ?
  and `status` = 1
UNION all
SELECT user1 as uid
from im_users_friends
where user2 = ?
  and `status` = 1
SQL;
            return Db::select($sql, [
                $uid,
                $uid,
            ]);
        } catch (\Throwable $throwable) {
            throw new $throwable();
        }
    }

    /**
     * 创建好友的申请.
     *
     * @param int $user_id 用户ID
     * @param int $friend_id 好友ID
     * @param string $remarks 好友申请备注
     */
    public function addFriendApply(int $user_id, int $friend_id, string $remarks): bool
    {
        // 判断是否是好友关系
        if (UsersFriends::isFriend($user_id, $friend_id)) {
            return true;
        }

        /**
         * @var UsersFriendsApply $result
         */
        $result = UsersFriendsApply::where('user_id', $user_id)->where('friend_id', $friend_id)->orderBy('id', 'desc')->first();
        if (! $result) {
            $result = UsersFriendsApply::create([
                'user_id' => $user_id,
                'friend_id' => $friend_id,
                'status' => 0,
                'remarks' => $remarks,
            ]);

            return $result ? true : false;
        }
        if ($result->status === 0) {
            $result->remarks = $remarks;
            $result->updated_at = date('Y-m-d H:i:s');
            $result->save();

            return true;
        }

        return false;
    }

    /**
     * 删除好友申请记录.
     *
     * @param int $uid 用户ID
     * @param int $applyId 好友申请ID
     */
    public function delFriendApply(int $uid, int $applyId): bool
    {
        return (bool) UsersFriendsApply::where('id', $applyId)->where('friend_id', $uid)->delete();
    }

    public function handleFriendApply(int $uid, int $applyId, $remarks = ''): bool
    {
        /**
         * @var UsersFriendsApply $info
         */
        $info = UsersFriendsApply::where('id', $applyId)->where('friend_id', $uid)->where('status', 0)->orderBy('id', 'desc')->first(['user_id', 'friend_id']);
        if (! $info) {
            return false;
        }

        DB::beginTransaction();
        try {
            $res = UsersFriendsApply::where('id', $applyId)->update(['status' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
            if (! $res) {
                throw new RuntimeException('更新好友申请表信息失败');
            }

            $user1 = $info->user_id;
            $user2 = $info->friend_id;
            if ($info->user_id > $info->friend_id) {
                [$user1, $user2] = [$info->friend_id, $info->user_id];
            }

            //查询是否存在好友记录
            /**
             * @var UsersFriends $friendResult
             */
            $friendResult = UsersFriends::select(['id', 'user1', 'user2', 'active', 'status'])->where('user1', '=', $user1)->where('user2', '=', $user2)->first();
            if ($friendResult) {
                $active = ($friendResult->user1 === $info->user_id && $friendResult->user2 === $info->friend_id) ? 1 : 2;
                if (! UserFriends::where('id', $friendResult->id)->update(['active' => $active, 'status' => 1])) {
                    throw new RuntimeException('更新好友关系信息失败');
                }
            } else {
                //好友昵称
                $friend_nickname = Users::where('id', $info->friend_id)->value('nickname');
                $insRes = UsersFriends::create([
                    'user1' => $user1,
                    'user2' => $user2,
                    'user1_remark' => $user1 === $uid ? $remarks : $friend_nickname,
                    'user2_remark' => $user2 === $uid ? $remarks : $friend_nickname,
                    'active' => $user1 === $uid ? 2 : 1,
                    'status' => 1,
                    'agree_time' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                if (! $insRes) {
                    throw new RuntimeException('创建好友关系失败');
                }
            }

            Db::commit();
        } catch (Exception $e) {
            dump($e->getMessage());
            Db::rollBack();
            return false;
        }

        return true;
    }

    /**
     * 解除好友关系.
     */
    public function removeFriend(int $uid, int $friendId): bool
    {
        if (! UsersFriends::isFriend($uid, $friendId)) {
            return false;
        }

        $data = ['status' => 0];

        // 用户ID比大小交换位置
        if ($uid > $friendId) {
            [$uid, $friendId] = [$friendId, $uid];
        }

        return (bool) UserFriends::where('user1', $uid)->where('user2', $friendId)->update($data);
    }

    /**
     * 获取用户好友申请记录.
     *
     * @param int $user_id 用户ID
     * @param int $page 分页数
     * @param int $page_size 分页大小
     */
    public function findApplyRecords(int $user_id, int $page = 1, int $page_size = 30): array
    {
        $rowsSqlObj = UsersFriendsApply::select([
            'users_friends_apply.id',
            'users_friends_apply.status',
            'users_friends_apply.remarks',
            'users.nickname',
            'users.avatar',
            'users.mobile',
            'users_friends_apply.user_id',
            'users_friends_apply.friend_id',
            'users_friends_apply.created_at',
        ]);

        $rowsSqlObj->leftJoin('users', 'users.id', '=', 'users_friends_apply.user_id');
        $rowsSqlObj->where('users_friends_apply.friend_id', $user_id);

        $count = $rowsSqlObj->count();
        $rows = [];
        if ($count > 0) {
            $rows = $rowsSqlObj->orderBy('users_friends_apply.id', 'desc')->forPage($page, $page_size)->get()->toArray();
        }

        return $this->getPagingRows($rows, $count, $page, $page_size);
    }

    /**
     * 编辑好友备注信息.
     */
    public function editFriendRemark(int $uid, int $friendId, string $remarks): bool
    {
        $data = [];
        if ($uid > $friendId) {
            [$uid, $friendId] = [$friendId, $uid];
            $data['user2_remark'] = $remarks;
        } else {
            $data['user1_remark'] = $remarks;
        }

        return (bool) UsersFriends::where('user1', $uid)->where('user2', $friendId)->update($data);
    }
}
