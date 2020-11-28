<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Component\Proxy;
use App\JsonRpc\Contract\InterfaceGroupService;
use App\Model\UsersChatList;
use App\Model\UsersFriends;
use App\Model\UsersGroup;
use App\Model\UsersGroupMember;
use App\Model\UsersGroupNotice;
use Hyperf\Utils\Coroutine;
use App\Helper\ValidateHelper;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class GroupController extends AbstractController
{
    /**
     *创建群聊
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create() : PsrResponseInterface
    {
        $params   = $this->request->all();
        $user     = $this->request->getAttribute('user');
        $friends  = array_filter(explode(',', $params['uids']));
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret      = $rpcGroup->create($user['id'], [
            'name'    => $params['group_name'],
            'avatar'  => '',
            'profile' => $params['group_profile'],
        ], array_unique($friends));
        //TODO 创建群组
        if (isset($ret['code']) && $ret['code'] === 1) {
            //群聊创建成功后需要创建聊天室并发送消息通知
            Coroutine::create(function () use ($ret)
            {
                $proxy = $this->container->get(Proxy::class);
                $proxy->groupNotify($ret['data']['record_id']);
            });
            return $this->response->success('创建群聊成功...', [
                'group_id' => $ret['data']['group_id']
            ]);
        }
        return $this->response->error('创建群聊失败，请稍后再试...');
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function detail() : PsrResponseInterface
    {
        $groupId = $this->request->input('group_id');
        if (!ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }
        $user = $this->request->getAttribute('user');
        $uid  = $user['id'] ?? 0;
        /**
         * @var UsersGroup|\App\Model\Users $groupInfo
         */
        $groupInfo = UsersGroup::leftJoin('users', 'users.id', '=', 'users_group.user_id')
                               ->where('users_group.id', $groupId)->where('users_group.status', 0)->first([
                'users_group.id',
                'users_group.user_id',
                'users_group.group_name',
                'users_group.group_profile',
                'users_group.avatar',
                'users_group.created_at',
                'users.nickname'
            ]);
        if (!$groupInfo) {
            return $this->response->success('success', []);
        }
        $notice = UsersGroupNotice::where('group_id', $groupId)->where('is_delete', 0)->orderBy('id', 'desc')->first(['title', 'content']);
        return $this->response->success('success', [
            'group_id'         => $groupInfo->id,
            'group_name'       => $groupInfo->group_name,
            'group_profile'    => $groupInfo->group_profile,
            'avatar'           => $groupInfo->avatar,
            'created_at'       => $groupInfo->created_at,
            'is_manager'       => $groupInfo->user_id === $uid,
            'manager_nickname' => $groupInfo->nickname,
            'visit_card'       => UsersGroupMember::visitCard($uid, $groupId),
            'not_disturb'      => UsersChatList::where('uid', $uid)->where('group_id', $groupId)->where('type', 2)->value('not_disturb') ?? 0,
            'notice'           => $notice ? $notice->toArray() : []
        ]);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function editDetail() : PsrResponseInterface
    {
        $user   = $this->request->getAttribute('user');
        $params = $this->request->hasInput(['group_id', 'group_name', 'group_profile', 'avatar']);
        if (count($params) !== 4 || empty($params['group_name'])) {
            return $this->response->parmasError();
        }
        $result = UsersGroup::where('id', $params['group_id'])->where('user_id', $user['id'] ?? 0)->update([
            'group_name'    => $params['group_name'],
            'group_profile' => $params['group_profile'],
            'avatar'        => $params['avatar']
        ]);
        return $result ? $this->response->success('信息修改成功...') : $this->response->error('信息修改失败...');
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function invite() : PsrResponseInterface
    {
        $groupId = $this->request->post('group_id');
        $uids    = array_filter(explode(',', $this->request->post('uids', '')));
        if (empty($uids) || !ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }
        $user     = $this->request->getAttribute('user');
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret      = $rpcGroup->invite($user['id'] ?? 0, $groupId, array_unique($uids));
        if (isset($ret['code']) && $ret['code'] === 1) {
            Coroutine::create(function () use ($ret)
            {
                $proxy = $this->container->get(Proxy::class);
                $proxy->groupNotify($ret['data']['record_id']);
            });
            return $this->response->success('好友已成功加入群聊...');
        }
        return $this->response->error('邀请好友加入群聊失败...');
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function removeMembers() : PsrResponseInterface
    {
        $groupId = $this->request->post('group_id');
        $mids    = $this->request->post('members_ids', []);
        if (empty($mids) || !ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }
        $user     = $this->request->getAttribute('user');
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret      = $rpcGroup->removeMember($groupId, $user['id'] ?? 0, $mids);
        if (isset($ret['code']) && $ret['code'] === 1) {
            Coroutine::create(function () use ($ret)
            {
                $proxy = $this->container->get(Proxy::class);
                $proxy->groupNotify($ret['data']['record_id']);
            });
            return $this->response->success('群聊用户已被移除...');
        }
        return $this->response->error('群聊用户移除失败...');
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dismiss() : PsrResponseInterface
    {
        $groupId = $this->request->post('group_id');
        if (!ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $user     = $this->request->getAttribute('user');
        $ret      = $rpcGroup->dismiss($groupId, $user['id'] ?? 0);
        if (isset($ret['code']) && $ret['code'] === 1) {
            // ... 推送群消息
            return $this->response->success('群聊已解散成功...');
        }
        return $this->response->error('群聊解散失败...');
    }

    public function secede() : PsrResponseInterface
    {
        $groupId = $this->request->post('group_id');
        if (!ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $user     = $this->request->getAttribute('user');
        $ret      = $rpcGroup->quit($user['id'] ?? 0, $groupId);
        if (isset($ret['code']) && $ret['code'] === 1) {
            Coroutine::create(function () use ($ret)
            {
                $proxy = $this->container->get(Proxy::class);
                $proxy->groupNotify($ret['data']['record_id']);
            });
            return $this->response->success('已成功退出群聊...');
        }
        return $this->response->error('退出群聊失败...');
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function setGroupCard() : PsrResponseInterface
    {
        $groupId    = $this->request->post('group_id');
        $visit_card = $this->request->post('visit_card');
        if (empty($visit_card) || !ValidateHelper::isInteger($groupId)) {
            return $this->response->parmasError();
        }
        $user     = $this->request->getAttribute('user');
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret      = $rpcGroup->setGroupCard($user['id'] ?? 0, $groupId, $visit_card);
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('设置成功');
        }
        return $this->response->error('设置失败');
    }

    public function getInviteFriends() : PsrResponseInterface
    {
        $group_id = $this->request->get('group_id', 0);
        $user     = $this->request->getAttribute('user');
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret      = $rpcGroup->getInviteFriends($user['id'] ?? 0, $group_id);
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('success', $ret['data']['friends']);
        }
        return $this->response->error('获取信息失败...');
    }

    public function getGroupMembers() : PsrResponseInterface
    {
        $group_id = $this->request->get('group_id', 0);
        $user     = $this->request->getAttribute('user');
        // 判断用户是否是群成员
        if (!UsersGroup::isMember($group_id, $user['id'] ?? 0)) {
            return $this->response->fail(403, '非法操作');
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret      = $rpcGroup->getGroupMembers($group_id, $user['id'] ?? 0);
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('success', $ret['data']['members']);
        }
        return $this->response->error('获取信息失败...');
    }

    public function getGroupNotices() : PsrResponseInterface
    {
        $group_id = $this->request->get('group_id', 0);
        $user     = $this->request->getAttribute('user');
        // 判断用户是否是群成员
        if (!UsersGroup::isMember($group_id, $user['id'] ?? 0)) {
            return $this->response->fail(403, '非法操作');
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret      = $rpcGroup->getGroupMembers($group_id, $user['id'] ?? 0);
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('success', $ret['data']['rows']);
        }
        return $this->response->error('获取信息失败...');
    }

    public function editNotice() : PsrResponseInterface
    {
        $data = $this->request->hasInput(['notice_id', 'group_id', 'title', 'content']);
        if (count($data) !== 4 || !ValidateHelper::isInteger($data['notice_id'])) {
            return $this->response->parmasError();
        }
        $user = $this->request->getAttribute('user');
        // 判断用户是否是管理员
        if (!UsersGroup::isManager($user['id'] ?? 0, $data['group_id'])) {
            return $this->response->fail(305, '非管理员禁止操作...');
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret      = $rpcGroup->editNotice($user['id'] ?? 0, $data['notice_id'], $data['group_id'], $data['title'], $data['content']);
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('修改群公告信息成功...');
        }
        return $this->response->error('修改群公告信息失败...');
    }

    public function deleteNotice() : PsrResponseInterface
    {
        $group_id  = $this->request->post('group_id');
        $notice_id = $this->request->post('notice_id');

        if (!ValidateHelper::isInteger($group_id) || !ValidateHelper::isInteger($notice_id)) {
            return $this->response->parmasError();
        }
        $user = $this->request->getAttribute('user');
        // 判断用户是否是管理员
        if (!UserGroup::isManager($user['id'] ?? 0, $group_id)) {
            return $this->response->fail(305, 'fail');
        }
        $rpcGroup = $this->container->get(InterfaceGroupService::class);
        $ret      = $rpcGroup->deleteNotice($user['id'] ?? 0, $group_id, $notice_id);
        if (isset($ret['code']) && $ret['code'] === 1) {
            return $this->response->success('删除公告成功...');
        }
        return $this->response->error('删除公告失败...');
    }

}
