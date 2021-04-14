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

use App\Cache\LastMsgCache;
use App\Model\ChatRecord;
use App\Model\ChatRecordsInvite;
use App\Model\Group;
use App\Model\GroupMember;
use App\Model\UsersChatList;
use Exception;
use Hyperf\DbConnection\Db;

class GroupService
{

    /**
     * 获取用户所在的群聊
     *
     * @param int $user_id 用户ID
     *
     * @return array
     */
    public function getGroups(int $user_id) : array
    {
        $items = GroupMember::join(Group::newModelInstance()->getTable(), 'group.id', '=', 'group_member.group_id')
                            ->where([
                                ['group_member.user_id', '=', $user_id],
                                ['group_member.is_quit', '=', 0]
                            ])
                            ->orderBy('id', 'desc')
                            ->get([
                                'group.id',
                                'group.group_name',
                                'group.avatar',
                                'group.profile',
                                'group_member.leader',
                            ])->toArray();

        $arr = UsersChatList::where([
            ['uid', '=', $user_id],
            ['type', '=', 2],
        ])->get(['group_id', 'not_disturb'])->keyBy('group_id')->toArray();

        foreach ($items as $key => $item) {
            $items[$key]['not_disturb'] = isset($arr[$item['id']]) ? $arr[$item['id']]['not_disturb'] : 0;
        }

        return $items;
    }

    public function getGroupUid(int $groupId) : array
    {
        return GroupMember::query()->where('group_id', $groupId)->get('user_id')->toArray();
    }

    /**
     * 创建群组.
     *
     * @param int   $uid       用户ID
     * @param array $groupInfo 群聊名称
     * @param array $friendIds 好友的用户ID
     *
     * @return array
     */
    public function create(int $uid, array $groupInfo, $friendIds = []) : ?array
    {
        $invite_ids   = implode(',', $friendIds);
        $friend_ids[] = $uid;
        $groupMember  = [];
        $chatList     = [];

        Db::beginTransaction();
        try {
            $insRes = Group::create([
                'creator_id' => $uid,
                'group_name' => $groupInfo['name'],
                'avatar'     => $groupInfo['avatar'],
                'profile'    => $groupInfo['profile'],
                'max_num'    => Group::MAX_MEMBER_NUM,
                'is_overt'   => 0,
                'is_mute'    => 0,
                'is_dismiss' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            foreach ($friend_ids as $fid) {
                $groupMember[] = [
                    'group_id'   => $insRes->id,
                    'user_id'    => $fid,
                    'leader'     => $fid === $uid ? 2 : 0,
                    'is_mute'    => 0,
                    'is_quit'    => 0,
                    'user_card'  => '',
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                $chatList[] = [
                    'type'       => 2,
                    'uid'        => $fid,
                    'friend_id'  => 0,
                    'group_id'   => $insRes->id,
                    'status'     => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            if (!Db::table('group_member')->insert($groupMember)) {
                throw new Exception('创建群成员信息失败');
            }

            if (!Db::table('users_chat_list')->insert($chatList)) {
                throw new Exception('创建群成员的聊天列表失败');
            }

            $result = ChatRecord::create([
                'msg_type'   => 3,
                'source'     => 2,
                'user_id'    => 0,
                'receive_id' => $insRes->id,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            ChatRecordsInvite::create([
                'record_id'       => $result->id,
                'type'            => 1,
                'operate_user_id' => $uid,
                'user_ids'        => $invite_ids
            ]);

            Db::commit();
            // 设置群聊消息缓存
            make(LastMsgCache::class)->set(['created_at' => date('Y-m-d H:i:s'), 'text' => '入群通知'], $insRes->id);
            return [true, ['record_id' => $result->id, 'group_id' => $insRes->id]];
        } catch (Exception $e) {
            Db::rollBack();
            return [false, 0];
        }
    }

    /**
     * 解散群组.
     *
     * @param int $groupId
     * @param int $uid
     *
     * @return bool
     */
    public function dismiss(int $groupId, int $uid) : bool
    {
        $group = Group::where('id', $groupId)->first(['creator_id', 'is_dismiss']);
        if (!$group || $group->creator_id !== $uid || $group->is_dismiss === 1) {
            return false;
        }

        DB::transaction(function () use ($groupId, $uid)
        {
            Group::where('id', $groupId)->where('creator_id', $uid)->update([
                'is_dismiss'   => 1,
                'dismissed_at' => date('Y-m-d H:i:s'),
            ]);

            GroupMember::where('group_id', $groupId)->update([
                'is_quit'    => 1,
                'deleted_at' => date('Y-m-d H:i:s'),
            ]);
        }, 2);

        return true;
    }

    /**
     * 邀请加入群组.
     *
     * @param array $friendIds
     *
     * @return array
     */
    public function invite(int $uid, int $groupId, $friendIds = []) : ?array
    {
        if (!$friendIds) {
            return [false, 0];
        }

        $info = GroupMember::where('group_id', $groupId)->where('user_id', $uid)->first(['id', 'is_quit']);

        // 判断主动邀请方是否属于聊天群成员
        if (!$info && $info->is_quit === 1) {
            return [false, 0];
        }

        $updateArr = $insertArr = $updateArr1 = $insertArr1 = [];

        $members = GroupMember::where('group_id', $groupId)->whereIn('user_id', $friendIds)->get(['id', 'user_id', 'is_quit'])->keyBy('user_id')->toArray();
        $chatArr = UsersChatList::where('group_id', $groupId)->whereIn('uid', $friendIds)->get(['id', 'uid', 'status'])->keyBy('uid')->toArray();

        foreach ($friendIds as $fid) {
            if (!isset($members[$fid])) {
                $insertArr[] = [
                    'group_id'   => $groupId,
                    'user_id'    => $fid,
                    'leader'     => 0,
                    'is_mute'    => 0,
                    'is_quit'    => 0,
                    'user_card'  => '',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            } elseif ($members[$fid]['status'] === 1) {
                $updateArr[] = $members[$fid]['id'];
            }

            if (!isset($chatArr[$fid])) {
                $insertArr1[] = [
                    'type'       => 2,
                    'uid'        => $fid,
                    'friend_id'  => 0,
                    'group_id'   => $groupId,
                    'status'     => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            } elseif ($chatArr[$fid]['status'] === 0) {
                $updateArr1[] = $chatArr[$fid]['id'];
            }
        }

        try {
            if ($updateArr) {
                GroupMember::whereIn('id', $updateArr)->update([
                    'leader'     => 0,
                    'is_mute'    => 0,
                    'is_quit'    => 0,
                    'user_card'  => '',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            if ($insertArr) {
                Db::table(GroupMember::newModelInstance()->getTable())->insert($insertArr);
            }

            if ($updateArr1) {
                UsersChatList::whereIn('id', $updateArr1)->update([
                    'status'     => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            if ($insertArr1) {
                Db::table('users_chat_list')->insert($insertArr1);
            }

            $result = ChatRecord::create([
                'msg_type'   => 3,
                'source'     => 2,
                'user_id'    => 0,
                'receive_id' => $groupId,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            ChatRecordsInvite::create([
                'record_id'       => $result->id,
                'type'            => 1,
                'operate_user_id' => $uid,
                'user_ids'        => implode(',', $friendIds)
            ]);

            Db::commit();
            make(LastMsgCache::class)->set(['created_at' => date('Y-m-d H:i:s'), 'text' => '入群通知'], $groupId);
            return [true, $result->id];
        } catch (Exception $e) {
            Db::rollBack();
            return [false, 0];
        }
    }

    /**
     * 退出群组.
     *
     * @return array
     * @throws \Exception
     */
    public function quit(int $uid, int $groupId) : ?array
    {
        if (Group::isManager($uid, $groupId)) {
            return [false, 0];
        }

        Db::beginTransaction();
        try {
            $count = GroupMember::where('group_id', $groupId)->where('user_id', $uid)->where('is_quit', 0)->update([
                'is_quit'    => 1,
                'deleted_at' => date('Y-m-d H:i:s'),
            ]);

            if ($count === 0) {
                throw new Exception('更新记录失败...');
            }

            $result = ChatRecord::create([
                'msg_type'   => 3,
                'source'     => 2,
                'user_id'    => 0,
                'receive_id' => $groupId,
                'content'    => $uid,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $record_id = $result->id;

            ChatRecordsInvite::create([
                'record_id'       => $result->id,
                'type'            => 2,
                'operate_user_id' => $uid,
                'user_ids'        => $uid
            ]);

            UsersChatList::where('uid', $uid)->where('type', 2)->where('group_id', $groupId)->update(['status' => 0]);

            Db::commit();
            return [true, $record_id];
        } catch (Exception $e) {
            Db::rollBack();
            return [false, 0];
        }
    }

    /**
     * 踢出群组(管理员特殊权限).
     */
    public function removeMember(int $groupId, int $uid, array $memberIds) : ?array
    {
        if (!Group::isManager($uid, $groupId)) {
            return [false, 0];
        }

        Db::beginTransaction();
        try {
            $count = GroupMember::where('group_id', $groupId)->whereIn('user_id', $memberIds)->where('is_quit', 0)->update([
                'is_quit'    => 1,
                'deleted_at' => date('Y-m-d H:i:s'),
            ]);

            if ($count === 0) {
                throw new Exception('更新记录失败...');
            }
            $result = ChatRecord::create([
                'msg_type'   => 3,
                'source'     => 2,
                'user_id'    => 0,
                'receive_id' => $groupId,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            ChatRecordsInvite::create([
                'record_id'       => $result->id,
                'type'            => 3,
                'operate_user_id' => $uid,
                'user_ids'        => implode(',', $memberIds)
            ]);

            UsersChatList::whereIn('uid', $memberIds)->where('group_id', $groupId)->update([
                'status'     => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            Db::commit();
            return [true, $result->id];
        } catch (Exception $e) {
            Db::rollBack();
            return [false, 0];
        }
    }
}
