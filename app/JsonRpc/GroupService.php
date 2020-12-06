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
namespace App\JsonRpc;

use App\Helper\ValidateHelper;
use App\JsonRpc\Contract\InterfaceGroupService;
use App\Model\UsersFriends;
use App\Model\UsersGroup;
use App\Model\UsersGroupMember;
use App\Model\UsersGroupNotice;
use Hyperf\RpcServer\Annotation\RpcService;
use Phper666\JWTAuth\JWT;
use Psr\Container\ContainerInterface;

/**
 * Class GroupService.
 * @RpcService(name="GroupService", protocol="jsonrpc-tcp-length-check", server="jsonrpc", publishTo="consul")
 */
class GroupService implements InterfaceGroupService
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Phper666\JWTAuth\JWT
     */
    protected $jwt;

    /**
     * @var \App\Service\GroupService
     */
    private $groupService;

    public function __construct(ContainerInterface $container, JWT $jwt, \App\Service\GroupService $groupService)
    {
        $this->container = $container;
        $this->groupService = $groupService;
        $this->jwt = $jwt;
    }

    /**
     * @param array $friendIds
     *
     * @return array|mixed
     */
    public function create(int $uid, array $groupInfo, $friendIds = []): array
    {
        if (empty($uid) || empty($groupInfo)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        [$bool, $data] = $this->groupService->create($uid, $groupInfo, array_unique($friendIds));
        if ($bool) {
            /*
             * $data = ['record_id' => $result, 'group_id' => $group]
             */
            return ['code' => 1, 'data' => $data, 'msg' => '群聊创建成功...'];
        }
        return ['code' => 0, 'msg' => '创建群聊失败，请稍后再试...'];
    }

    /**
     * @return array|mixed
     */
    public function dismiss(int $groupId, int $uid): array
    {
        if (empty($groupId) || empty($uid)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (! ValidateHelper::isInteger($groupId) || ! ValidateHelper::isInteger($uid)) {
            return ['code' => 0, 'msg' => '参数错误...'];
        }
        $bool = $this->groupService->dismiss($groupId, $uid);
        if ($bool) {
            //TODO 推送群消息
        }
        return $bool ? ['code' => 1, 'msg' => '群聊已解散成功...'] : ['code' => 0, 'msg' => '群聊解散失败...'];
    }

    /**
     * @param array $friendIds
     *
     * @return array|mixed
     */
    public function invite(int $uid, int $groupId, $friendIds = []): array
    {
        if (empty($uid) || empty($groupId)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (! ValidateHelper::isInteger($groupId) || ! ValidateHelper::isInteger($uid)) {
            return ['code' => 0, 'msg' => '参数错误...'];
        }
        [$bool, $record] = $this->groupService->invite($uid, $groupId, array_unique($friendIds));
        if ($bool) {
            return [
                'code' => 1,
                'msg' => '好友已成功加入群聊...',
                'data' => [
                    'record_id' => $record,
                ],
            ];
        }
        return [
            'code' => 0,
            'msg' => '加入群聊失败...',
        ];
    }

    /**
     * @throws \Exception
     * @return array|mixed
     */
    public function quit(int $uid, int $groupId): array
    {
        if (empty($uid) || empty($groupId)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (! ValidateHelper::isInteger($groupId) || ! ValidateHelper::isInteger($uid)) {
            return ['code' => 0, 'msg' => '参数错误...'];
        }
        [$bool, $record] = $this->groupService->quit($uid, $groupId);
        if ($bool) {
            return [
                'code' => 1,
                'msg' => '已成功退出群聊...',
                'data' => [
                    'record_id' => $record,
                ],
            ];
        }
        return [
            'code' => 0,
            'msg' => '退出群聊失败...',
        ];
    }

    /**
     * @throws \Exception
     * @return array|mixed
     */
    public function removeMember(int $groupId, int $uid, array $memberIds): array
    {
        if (empty($uid) || empty($groupId) || empty($memberIds)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        //TODO 此处ValidateHelper::isIndexArray可能会验证失败
        if (! ValidateHelper::isInteger($groupId) || ! ValidateHelper::isInteger($uid) || ! ValidateHelper::isIndexArray($memberIds)) {
            return ['code' => 0, 'msg' => '参数错误...'];
        }
        [$bool, $record] = $this->groupService->removeMember($uid, $groupId, $memberIds);
        if ($bool) {
            return [
                'code' => 1,
                'msg' => '群聊用户已被移除..',
                'data' => [
                    'record_id' => $record,
                ],
            ];
        }
        return [
            'code' => 0,
            'msg' => '群聊用户移除失败...',
        ];
    }

    /**
     * 设置用户群名片.
     *
     * @return array|mixed
     */
    public function setGroupCard(int $uid, int $groupId, string $visitCard): array
    {
        if (empty($uid) || empty($groupId) || empty($visitCard)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (! ValidateHelper::isInteger($groupId) || ! ValidateHelper::isInteger($uid)) {
            return ['code' => 0, 'msg' => '参数错误...'];
        }
        if (UsersGroupMember::where('group_id', $groupId)->where('user_id', $uid)->where('status', 0)->update(['visit_card' => $visitCard])) {
            return ['code' => 1, 'msg' => '设置成功...'];
        }
        return ['code' => 0, 'msg' => '设置失败...'];
    }

    /**
     * 获取用户可邀请加入群组的好友列表.
     *
     * @return array|mixed
     */
    public function getInviteFriends(int $uid, int $groupId): array
    {
        if (! ValidateHelper::isInteger($groupId) || ! ValidateHelper::isInteger($uid)) {
            return ['code' => 0, 'msg' => '参数错误...'];
        }
        $friends = UsersFriends::getUserFriends($uid);
        if ($groupId > 0 && $friends) {
            if ($ids = UsersGroupMember::getGroupMemberIds($groupId)) {
                foreach ($friends as $k => $item) {
                    if (in_array($item['id'], $ids, true)) {
                        unset($friends[$k]);
                    }
                }
            }
            $friends = array_values($friends);
        }
        return ['code' => 1, 'msg' => '获取好友成功...', 'data' => ['friends' => $friends]];
    }

    /**
     * 获取群组成员列表.
     *
     * @return array|mixed
     */
    public function getGroupMembers(int $groupId, int $uid): array
    {
        if (empty($uid) || empty($groupId)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (! UsersGroup::isMember($groupId, $uid)) {
            return ['code' => 0, 'msg' => '非法操作'];
        }
        $members = UsersGroupMember::select([
            'users_group_member.id',
            'users_group_member.group_owner as is_manager',
            'users_group_member.visit_card',
            'users_group_member.user_id',
            'users.avatar',
            'users.nickname',
            'users.gender',
            'users.motto',
        ])
            ->leftJoin('users', 'users.id', '=', 'users_group_member.user_id')
            ->where([
                ['users_group_member.group_id', '=', $groupId],
                ['users_group_member.status', '=', 0],
            ])->orderBy('is_manager', 'desc')->get()->toArray();
        return [
            'code' => 1,
            'data' => [
                'members' => $members,
            ],
        ];
    }

    /**
     * 获取群组公告列表.
     *
     * @return array|mixed
     */
    public function getGroupNotices(int $uid, int $groupId): array
    {
        if (empty($uid) || empty($groupId)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (! UsersGroup::isMember($groupId, $uid)) {
            return ['code' => 0, 'msg' => '非法操作'];
        }
        $rows = UsersGroupNotice::leftJoin('users', 'users.id', '=', 'users_group_notice.user_id')
            ->where([['users_group_notice.group_id', '=', $groupId], ['users_group_notice.is_delete', '=', 0]])
            ->orderBy('users_group_notice.id', 'desc')
            ->get([
                'users_group_notice.id',
                'users_group_notice.user_id',
                'users_group_notice.title',
                'users_group_notice.content',
                'users_group_notice.created_at',
                'users_group_notice.updated_at',
                'users.avatar',
                'users.nickname',
            ])->toArray();
        return ['code' => 1, 'data' => $rows];
    }

    /**
     * 编辑群公告.
     *
     * @return array|mixed
     */
    public function editNotice(int $uid, int $noticeid, int $groupId, string $title, string $content): array
    {
        if (empty($uid) || empty($groupId) || empty($title) || empty($content)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (! UsersGroup::isManager($uid, $groupId)) {
            return ['code' => 0, 'msg' => '非管理员禁止操作...'];
        }
        // 判断是否是新增数据
        if (empty($noticeid)) {
            $result = UsersGroupNotice::create([
                'group_id' => $groupId,
                'title' => $title,
                'content' => $content,
                'user_id' => $uid,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $result) {
                return ['code' => 0, 'msg' => '添加群公告信息失败...'];
            }

            // ... 推送群消息
            return ['code' => 1, 'msg' => '添加群公告信息成功...'];
        }
        $ret = UsersGroupNotice::where('id', $noticeid)->update(['title' => $title, 'content' => $content, 'updated_at' => date('Y-m-d H:i:s')]);
        return $ret ? ['code' => 1, 'msg' => '修改群公告信息成功...'] : ['code' => 0, 'msg' => '修改群公告信息成功...'];
    }

    /**
     *  删除群公告(软删除).
     *
     * @return array|mixed
     */
    public function deleteNotice(int $uid, int $groupId, int $noticeId): array
    {
        if (empty($uid) || empty($groupId) || empty($noticeId)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (! UsersGroup::isManager($uid, $groupId)) {
            return ['code' => 0, 'msg' => '非管理员禁止操作...'];
        }
        $result = UsersGroupNotice::where('id', $noticeId)->where('group_id', $groupId)->update(['is_delete' => 1, 'deleted_at' => date('Y-m-d H:i:s')]);
        return $result ? ['code' => 1, 'msg' => '删除公告成功...'] : ['code' => 0, 'msg' => '删除公告失败...'];
    }

    /**
     * 获取群信息接口.
     *
     * @return array|mixed
     */
    public function detail(int $uid, int $groupId): array
    {
        if (empty($uid) || empty($groupId)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        /**
         * @var \App\Model\Users|UsersGroup $groupInfo
         */
        $groupInfo = UsersGroup::leftJoin('users', 'users.id', '=', 'users_group.user_id')
            ->where('users_group.id', $groupId)->where('users_group.status', 0)->first([
                'users_group.id',
                'users_group.user_id',
                'users_group.group_name',
                'users_group.group_profile',
                'users_group.avatar',
                'users_group.created_at',
                'users.nickname',
            ]);
        if (! $groupInfo) {
            return ['code' => 1, 'data' => []];
        }
        $notice = UsersGroupNotice::where('group_id', $groupId)->where('is_delete', 0)->orderBy('id', 'desc')->first(['title', 'content']);
        return [
            'code' => 1,
            'data' => [
                'group_id' => $groupInfo->id,
                'group_name' => $groupInfo->group_name,
                'group_profile' => $groupInfo->group_profile,
                'avatar' => $groupInfo->avatar,
                'created_at' => $groupInfo->created_at,
                'is_manager' => $groupInfo->user_id === $uid,
                'manager_nickname' => $groupInfo->nickname,
                'visit_card' => UserGroupMember::visitCard($uid, $groupId),
                'not_disturb' => UserChatList::where('uid', $uid)->where('group_id', $groupId)->where('type', 2)->value('not_disturb') ?? 0,
                'notice' => $notice ? $notice->toArray() : [],
            ],
        ];
    }
}
