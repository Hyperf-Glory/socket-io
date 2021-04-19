<?php
declare(strict_types = 1);
namespace App\Controller\Http;

use App\Controller\AbstractController;
use App\Model\Group;
use App\Model\GroupMember;
use App\Model\UsersChatList;
use App\Model\UsersGroupNotice;
use App\SocketIO\Proxy\GroupNotify;
use Hyperf\Utils\Coroutine;
use App\Service\GroupService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GroupController extends AbstractController
{

    private GroupService $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    /**
     * 创建群聊
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create(RequestInterface $request) : ResponseInterface
    {
        $params  = $request->all();
        $friends = array_filter(explode(',', $params['uids']));
        [$bool, $data] = $this->groupService->create($this->uid(), [
            'name'    => $params['group_name'],
            'avatar'  => '',
            'profile' => $params['group_profile'],
        ], array_unique($friends));
        if ($bool) {
            //群聊创建成功后需要创建聊天室并发送消息通知
            Coroutine::create(function () use ($data)
            {
                $service = make(GroupNotify::class);
                $service->process($data['data']['record_id']);
            });
            return $this->response->success('创建群聊成功...', [
                'group_id' => $data['data']['group_id'],
            ]);
        }
        return $this->response->error('创建群聊失败，请稍后再试...');
    }

    /**
     * 群聊详情
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function detail() : ResponseInterface
    {
        $groupId = (int)$this->request->input('group_id');

        /**
         * @var \App\Model\User|\App\Model\Group $groupInfo
         */
        $groupInfo = Group::leftJoin('users', 'users.id', '=', 'users_group.user_id')
                          ->where('users_group.id', $groupId)->where('users_group.status', 0)->first([
                'users_group.id',
                'users_group.user_id',
                'users_group.group_name',
                'users_group.group_profile',
                'users_group.avatar',
                'users_group.created_at',
                'users.nickname',
            ]);
        if (!$groupInfo) {
            return $this->response->success('success', []);
        }
        $notice = UsersGroupNotice::where('group_id', $groupId)->where('is_delete', 0)->orderBy('id', 'desc')->first(['title', 'content']);
        return $this->response->success('success', [
            'group_id'         => $groupInfo->id,
            'group_name'       => $groupInfo->group_name,
            'group_profile'    => $groupInfo->profile,
            'avatar'           => $groupInfo->avatar,
            'created_at'       => $groupInfo->created_at,
            'is_manager'       => $groupInfo->creator_id === $this->uid(),
            'manager_nickname' => $groupInfo->nickname,
            'visit_card'       => GroupMember::visitCard($this->uid(), $groupId),
            'not_disturb'      => UsersChatList::where('uid', $this->uid())->where('group_id', $groupId)->where('type', 2)->value('not_disturb') ?? 0,
            'notice'           => $notice ? $notice->toArray() : [],
        ]);
    }

    /**
     * 邀请好友加入群聊
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function invite() : ResponseInterface
    {
        $groupId = (int)$this->request->post('group_id');
        $uids    = array_filter(explode(',', $this->request->post('uids', '')));
        [$bool, $record] = $this->groupService->invite($this->uid(), $groupId, array_unique($uids));
        if ($bool) {
            Coroutine::create(function () use ($record)
            {
                $service = make(GroupNotify::class);
                $service->process($record);
            });
            return $this->response->success('好友已成功加入群聊...');
        }
        return $this->response->error('邀请好友加入群聊失败...');
    }

    /**
     * 踢出群组(管理员特殊权限).
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function removeMembers() : ResponseInterface
    {
        $groupId = (int)$this->request->post('group_id');
        $mids    = $this->request->post('members_ids', []);
        [$bool, $ret] = $this->groupService->removeMember($groupId, $this->uid(), $mids);
        if ($bool) {
            Coroutine::create(function () use ($ret)
            {
                $service = make(GroupNotify::class);
                $service->process($ret);
            });
            return $this->response->success('群聊用户已被移除...');
        }
        return $this->response->error('群聊用户移除失败...');
    }

    /**
     * 解散群聊
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dismiss() : ResponseInterface
    {
        $groupId = (int)$this->request->post('group_id');

        [$bool, $ret] = $this->groupService->dismiss($groupId, $this->uid());

        if ($bool) {
            //... TODO 推送群消息
            Coroutine::create(static function () use ($ret)
            {

            });
            return $this->response->success('群聊已解散成功...');
        }
        return $this->response->error('群聊解散失败...');
    }

    /**
     * 退出群组.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function secede() : ResponseInterface
    {
        $groupId = (int)$this->request->post('group_id');
        try {
            [$bool, $ret] = $this->groupService->quit($this->uid(), $groupId);
            if ($bool) {
                Coroutine::create(function () use ($ret)
                {
                    $service = make(GroupNotify::class);
                    $service->process($ret);
                });
                return $this->response->success('已成功退出群聊...');
            }
        } catch (\Exception $e) {
        }
        return $this->response->error('退出群聊失败...');
    }

    /**
     * 设置群名片
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function setGroupCard() : ResponseInterface
    {
        $groupId    = (int)$this->request->post('group_id');
        $visit_card = $this->request->post('visit_card');
        if (GroupMember::where('group_id', $groupId)->where('user_id', $this->uid)->where('is_quit', 0)->update(['visit_card' => $visit_card])) {
            return $this->response->success('设置成功');
        }
        return $this->response->error('设置失败');
    }

    public function getInviteFriends()
    {

    }

    public function getGroupMembers()
    {

    }

    public function getGroupNotices()
    {

    }

    public function editNotice()
    {

    }

    public function deleteNotice()
    {

    }
}

