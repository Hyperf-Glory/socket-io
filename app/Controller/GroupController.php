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
namespace App\Controller;

use App\Component\Proxy;
use App\Helper\ValidateHelper;
use App\JsonRpc\Contract\InterfaceGroupService;
use App\Model\UsersChatList;
use App\Model\UsersGroup;
use App\Model\UsersGroupMember;
use App\Model\UsersGroupNotice;
use Hyperf\Utils\Coroutine;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class GroupController extends AbstractController
{
    /**
     *创建群聊.
     */
    public function create(): PsrResponseInterface
    {
        $params = $this->request->all();
        $friends = array_filter(explode(',', $params['uids']));
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->create($this->uid(), [
            'name' => $params['group_name'],
            'avatar' => '',
            'profile' => $params['group_profile'],
        ], array_unique($friends));
        if (isset($ret['code']) && $ret['code'] === 1) {
            //群聊创建成功后需要创建聊天室并发送消息通知
            Coroutine::create(function () use ($ret) {
                $proxy = $this->container->get(Proxy::class);
                $proxy->groupNotify($ret['data']['record_id']);
            });
            return $this->response->success('创建群聊成功...', [
                'group_id' => $ret['data']['group_id'],
            ]);
        }
        return $this->response->error('创建群聊失败，请稍后再试...');
    }

    public function detail(): PsrResponseInterface
    {
        $groupId = (int) $this->request->input('group_id');
        if (! ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
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
            return $this->response->success('success', []);
        }
        $notice = UsersGroupNotice::where('group_id', $groupId)->where('is_delete', 0)->orderBy('id', 'desc')->first(['title', 'content']);
        return $this->response->success('success', [
            'group_id' => $groupInfo->id,
            'group_name' => $groupInfo->group_name,
            'group_profile' => $groupInfo->group_profile,
            'avatar' => $groupInfo->avatar,
            'created_at' => $groupInfo->created_at,
            'is_manager' => $groupInfo->user_id === $this->uid(),
            'manager_nickname' => $groupInfo->nickname,
            'visit_card' => UsersGroupMember::visitCard($this->uid(), $groupId),
            'not_disturb' => UsersChatList::where('uid', $this->uid())->where('group_id', $groupId)->where('type', 2)->value('not_disturb') ?? 0,
            'notice' => $notice ? $notice->toArray() : [],
        ]);
    }

    public function editDetail(): PsrResponseInterface
    {
        $params = $this->request->inputs(['group_id', 'group_name', 'group_profile', 'avatar']);
        if (count($params) !== 4 || empty($params['group_name'])) {
            return $this->response->parmasError();
        }
        $result = UsersGroup::where('id', (int) $params['group_id'])->where('user_id', $this->uid())->update([
            'group_name' => $params['group_name'],
            'group_profile' => $params['group_profile'],
            'avatar' => $params['avatar'],
        ]);
        return $result ? $this->response->success('信息修改成功...') : $this->response->error('信息修改失败...');
    }

    public function invite(): PsrResponseInterface
    {
        $groupId = (int) $this->request->post('group_id');
        $uids = array_filter(explode(',', $this->request->post('uids', '')));
        if (empty($uids) || ! ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }

        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->invite($this->uid(), $groupId, array_unique($uids));
        if (isset($ret['code']) && $ret['code'] === 1) {
            Coroutine::create(function () use ($ret) {
                $proxy = $this->container->get(Proxy::class);
                $proxy->groupNotify($ret['data']['record_id']);
            });
            return $this->response->success('好友已成功加入群聊...');
        }
        return $this->response->error('邀请好友加入群聊失败...');
    }

    public function removeMembers(): PsrResponseInterface
    {
        $groupId = (int) $this->request->post('group_id');
        $mids = $this->request->post('members_ids', []);
        if (empty($mids) || ! ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }

        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->removeMember($groupId, $this->uid(), $mids);
        if (isset($ret['code']) && $ret['code'] === 1) {
            Coroutine::create(function () use ($ret) {
                $proxy = $this->container->get(Proxy::class);
                $proxy->groupNotify($ret['data']['record_id']);
            });
            return $this->response->success('群聊用户已被移除...');
        }
        return $this->response->error('群聊用户移除失败...');
    }

    public function dismiss(): PsrResponseInterface
    {
        $groupId = (int) $this->request->post('group_id');
        if (! ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->dismiss($groupId, $this->uid());
        if (isset($ret['code']) && $ret['code'] === 1) {
            // ... 推送群消息
            return $this->response->success('群聊已解散成功...');
        }
        return $this->response->error('群聊解散失败...');
    }

    public function secede(): PsrResponseInterface
    {
        $groupId = (int) $this->request->post('group_id');
        if (! ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->quit($this->uid(), $groupId);
        if (isset($ret['code']) && $ret['code'] === 1) {
            Coroutine::create(function () use ($ret) {
                $proxy = $this->container->get(Proxy::class);
                $proxy->groupNotify($ret['data']['record_id']);
            });
            return $this->response->success('已成功退出群聊...');
        }
        return $this->response->error('退出群聊失败...');
    }

    public function setGroupCard(): PsrResponseInterface
    {
        $groupId = (int) $this->request->post('group_id');
        $visit_card = $this->request->post('visit_card');
        if (empty($visit_card) || ! ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->setGroupCard($this->uid(), $groupId, $visit_card);
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('设置成功');
        }
        return $this->response->error('设置失败');
    }

    public function getInviteFriends(): PsrResponseInterface
    {
        $group_id = (int) $this->request->input('group_id', 0);
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->getInviteFriends($this->uid(), $group_id);
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('success', $ret['data']['friends']);
        }
        return $this->response->error('获取信息失败...');
    }

    public function getGroupMembers(): PsrResponseInterface
    {
        $group_id = (int) $this->request->input('group_id', 0);

        // 判断用户是否是群成员
        if (! UsersGroup::isMember($group_id, $this->uid())) {
            return $this->response->fail(403, '非法操作');
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->getGroupMembers($group_id, $this->uid());
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('success', $ret['data']['members']);
        }
        return $this->response->error('获取信息失败...');
    }

    public function getGroupNotices(): PsrResponseInterface
    {
        $group_id = (int) $this->request->input('group_id', 0);

        // 判断用户是否是群成员
        if (! UsersGroup::isMember($group_id, $this->uid())) {
            return $this->response->fail(403, '非法操作');
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->getGroupMembers($group_id, $this->uid());
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('success', $ret['data']['members']);
        }
        return $this->response->error('获取信息失败...');
    }

    public function editNotice(): PsrResponseInterface
    {
        $data = $this->request->hasInput(['notice_id', 'group_id', 'title', 'content']);
        if (count($data) !== 4 || ! ValidateHelper::isInteger($data['notice_id'])) {
            return $this->response->parmasError();
        }

        // 判断用户是否是管理员
        if (! UsersGroup::isManager($this->uid(), (int) $data['group_id'])) {
            return $this->response->fail(305, '非管理员禁止操作...');
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->editNotice($this->uid(), (int) $data['notice_id'], (int) $data['group_id'], $data['title'], $data['content']);
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('修改群公告信息成功...');
        }
        return $this->response->error('修改群公告信息失败...');
    }

    public function deleteNotice(): PsrResponseInterface
    {
        $group_id = (int) $this->request->post('group_id');
        $notice_id = (int) $this->request->post('notice_id');

        if (! ValidateHelper::isInteger($group_id) || ! ValidateHelper::isInteger($notice_id)) {
            return $this->response->parmasError();
        }
        // 判断用户是否是管理员
        if (! UserGroup::isManager($this->uid(), $group_id)) {
            return $this->response->fail(305, 'fail');
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret = $rpcGroup->deleteNotice($this->uid(), $group_id, $notice_id);
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('删除公告成功...');
        }
        return $this->response->error('删除公告失败...');
    }
}
