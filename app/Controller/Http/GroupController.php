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
}

