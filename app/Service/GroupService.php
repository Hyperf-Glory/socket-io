<?php

declare(strict_types=1);
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

use App\Cache\LastMsgCache;
use App\Model\ChatRecords;
use App\Model\ChatRecordsInvite;
use App\Model\UsersChatList;
use App\Model\UsersGroup;
use App\Model\UsersGroupMember;
use Exception;
use Hyperf\DbConnection\Db;
use RuntimeException;

class GroupService
{
    public function getGroupUid(int $groupId)
    {
        return UsersGroupMember::query()->where('group_id', $groupId)->get('user_id')->toArray();
    }

    /**
     * 创建群组.
     *
     * @param int $uid 用户ID
     * @param array $groupInfo 群聊名称
     * @param array $friendIds 好友的用户ID
     *
     * @return array
     */
    public function create(int $uid, array $groupInfo, $friendIds = [])
    {
        $friendIds[] = $uid;
        $groupMember = [];
        $chatList = [];
        Db::beginTransaction();
        try {
            $group = UsersGroup::create([
                'user_id' => $uid,
                'group_name' => $groupInfo['name'],
                'avatar' => $groupInfo['avatar'],
                'group_profile' => $groupInfo['profile'],
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            if (! $group) {
                throw new RuntimeException('创建群失败!');
            }

            foreach ($friendIds as $k => $_uid) {
                $groupMember[] = [
                    'group_id' => $group->id,
                    'user_id' => $_uid,
                    'group_owner' => ($k === 0) ? 1 : 0,
                    'status' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                $chatList[] = [
                    'type' => 2,
                    'uid' => $_uid,
                    'friend_id' => 0,
                    'group_id' => $group->id,
                    'status' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            if (! Db::table('users_group_member')->insert($groupMember)) {
                throw new RuntimeException('创建群成员信息失败');
            }
            if (! Db::table('users_chat_list')->insert($chatList)) {
                throw new RuntimeException('创建群成员的聊天列表失败');
            }

            $result = ChatRecords::create([
                'msg_type' => 3,
                'source' => 2,
                'user_id' => 0,
                'receive_id' => $group->id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $result) {
                throw new RuntimeException('创建群成员的聊天列表失败');
            }
            ChatRecordsInvite::insert([
                'record_id' => $result->id,
                'type' => 1,
                'operate_user_id' => $uid,
                'user_ids' => implode(',', $friendIds),
            ]);
            Db::commit();
            LastMsgCache::set(['created_at' => date('Y-m-d H:i:s'), 'text' => '入群通知'], $group->id, 0);
            return [true, ['record_id' => $result->id, 'group_id' => $group->id]];
        } catch (\Throwable $throwable) {
            Db::rollBack();
            return [false, 0];
        }
    }

    /**
     * 解散群组.
     *
     * @return bool
     */
    public function dismiss(int $groupId, int $uid)
    {
        if (! UsersGroup::where('id', $groupId)->where('status', 0)->exists()) {
            return false;
        }

        //判断执行者是否属于群主
        if (! UsersGroup::isManager($uid, $groupId)) {
            return false;
        }
        DB::beginTransaction();
        try {
            UsersGroup::where('id', $groupId)->update(['status' => 1]);
            UsersGroupMember::where('group_id', $groupId)->update(['status' => 1]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }

        return true;
    }

    /**
     * 邀请加入群组.
     *
     * @param array $friendIds
     *
     * @return array
     */
    public function invite(int $uid, int $groupId, $friendIds = [])
    {
        /**
         * @var UsersGroupMember $info
         */
        $info = UsersGroupMember::select(['id', 'status'])->where('group_id', $groupId)->where('user_id', $uid)->first();

        //判断主动邀请方是否属于聊天群成员
        if (! $info && $info->status === 1) {
            return [false, 0];
        }

        if (empty($friendIds)) {
            return [false, 0];
        }

        $updateArr = $insertArr = $updateArr1 = $insertArr1 = [];

        $members = UsersGroupMember::where('group_id', $groupId)->whereIn('user_id', $friendIds)->get(['id', 'user_id', 'status'])->keyBy('user_id')->toArray();
        $chatArr = UsersChatList::where('group_id', $groupId)->whereIn('uid', $friendIds)->get(['id', 'uid', 'status'])->keyBy('uid')->toArray();

        foreach ($friendIds as $id) {
            if (! isset($members[$id])) {//存在聊天群成员记录
                $insertArr[] = ['group_id' => $groupId, 'user_id' => $id, 'group_owner' => 0, 'status' => 0, 'created_at' => date('Y-m-d H:i:s')];
            } elseif ($members[$id]['status'] === 1) {
                $updateArr[] = $members[$id]['id'];
            }

            if (! isset($chatArr[$id])) {
                $insertArr1[] = ['type' => 2, 'uid' => $id, 'friend_id' => 0, 'group_id' => $groupId, 'status' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
            } elseif ($chatArr[$id]['status'] === 0) {
                $updateArr1[] = $chatArr[$id]['id'];
            }
        }

        try {
            if ($updateArr) {
                UsersGroupMember::whereIn('id', $updateArr)->update(['status' => 0]);
            }
            if ($insertArr) {
                DB::table('users_group_member')->insert($insertArr);
            }
            if ($updateArr1) {
                UsersChatList::whereIn('id', $updateArr1)->update(['status' => 1, 'created_at' => date('Y-m-d H:i:s')]);
            }

            if ($insertArr1) {
                DB::table('users_chat_list')->insert($insertArr1);
            }
            $result = ChatRecords::create([
                'msg_type' => 3,
                'source' => 2,
                'user_id' => 0,
                'receive_id' => $groupId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            if (! $result) {
                throw new RuntimeException('添加群通知记录失败1');
            }
            $result2 = ChatRecordsInvite::create([
                'record_id' => $result->id,
                'type' => 1,
                'operate_user_id' => $uid,
                'user_ids' => implode(',', $friendIds),
            ]);
            if (! $result2) {
                throw new Exception('添加群通知记录失败2');
            }
            DB::commit();
            LastMsgCache::set(['created_at' => date('Y-m-d H:i:s'), 'text' => '入群通知'], $groupId, 0);
            return [true, $result->id];
        } catch (\Throwable $throwable) {
            dump($throwable->getMessage());
            DB::rollBack();
            return [false, 0];
        }
    }

    /**
     * 退出群组.
     *
     * @throws \Exception
     * @return array
     */
    public function quit(int $uid, int $groupId)
    {
        $recordId = 0;
        DB::beginTransaction();
        try {
            $res = UsersGroupMember::where('group_id', $groupId)->where('user_id', $uid)->where('group_owner', 0)->update(['status' => 1]);
            if ($res) {
                UsersChatList::where('uid', $uid)->where('type', 2)->where('group_id', $groupId)->update(['status' => 0]);

                $result = ChatRecords::create([
                    'msg_type' => 3,
                    'source' => 2,
                    'user_id' => 0,
                    'receive_id' => $groupId,
                    'content' => $uid,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                if (! $result) {
                    throw new RuntimeException('添加群通知记录失败 : quitGroupChat');
                }

                $result2 = ChatRecordsInvite::create([
                    'record_id' => $result->id,
                    'type' => 2,
                    'operate_user_id' => $uid,
                    'user_ids' => $uid,
                ]);

                if (! $result2) {
                    throw new RuntimeException('添加群通知记录失败2  : quitGroupChat');
                }

                $recordId = $result->id;
            }

            DB::commit();
            return [true, $recordId];
        } catch (Exception $e) {
            DB::rollBack();
            return [false, 0];
        }
    }

    /**
     * 踢出群组(管理员特殊权限).
     */
    public function removeMember(int $groupId, int $uid, array $memberIds)
    {
        if (! UsersGroup::isManager($uid, $groupId)) {
            return [false, 0];
        }

        DB::beginTransaction();
        try {
            //更新用户状态
            if (! UsersGroupMember::where('group_id', $groupId)->whereIn('user_id', $memberIds)->where('group_owner', 0)->update(['status' => 1])) {
                throw new RuntimeException('修改群成员状态失败');
            }

            $result = ChatRecords::create([
                'msg_type' => 3,
                'source' => 2,
                'user_id' => 0,
                'receive_id' => $groupId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $result) {
                throw new RuntimeException('添加群通知记录失败1');
            }

            $result2 = ChatRecordsInvite::create([
                'record_id' => $result->id,
                'type' => 3,
                'operate_user_id' => $uid,
                'user_ids' => implode(',', $memberIds),
            ]);

            if (! $result2) {
                throw new RuntimeException('添加群通知记录失败2');
            }

            DB::commit();
            return [true, $result->id];
        } catch (Exception $e) {
            DB::rollBack();
            return [false, 0];
        }
    }
}
