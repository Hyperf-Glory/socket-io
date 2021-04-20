<?php
declare(strict_types = 1);
namespace App\Controller\Http;

use App\Controller\AbstractController;
use App\Model\Group;
use App\Model\GroupMember;
use App\Model\User;
use App\Model\UsersChatList;
use App\Model\UsersFriend;
use App\Model\UsersGroupNotice;
use App\SocketIO\Proxy\GroupNotify;
use Hyperf\Utils\Coroutine;
use App\Service\GroupService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;

class GroupController extends AbstractController
{

    private GroupService $groupService;
    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(GroupService $groupService, ValidatorFactoryInterface $validationFactory)
    {
        $this->groupService      = $groupService;
        $this->validationFactory = $validationFactory;
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

    /**
     * 获取可邀请加入群组的好友列表
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getInviteFriends() : ResponseInterface
    {
        $groupId = (int)$this->request->input('group_id', 0);
        $friends = UsersFriend::getUserFriends($this->uid());
        if ($groupId > 0 && $friends) {
            if ($ids = GroupMember::getGroupMemberIds($groupId)) {
                foreach ($friends as $k => $value) {
                    if (in_array($value['id'], $ids, true)) {
                        unset($friends[$k]);
                    }
                }
            }
            $friends = array_values($friends);
            return $this->response->success('success', $friends);
        }
        return $this->response->error('获取信息失败...');
    }

    /**
     * 获取群组成员列表
     */
    public function getGroupMembers() : ResponseInterface
    {
        $groupId = (int)$this->request->input('group_id', 0);
        if (!Group::isMember($groupId, $this->uid())) {
            return $this->response->fail(403, '非法操作');
        }
        $members = GroupMember::select([
            'group_member.id',
            'group_member.leader',
            'group_member.user_card',
            'group_member.user_id',
            'users.avatar',
            'users.nickname',
            'users.gender',
            'users.motto',
        ])
                              ->leftJoin('users', 'users.id', '=', 'group_member.user_id')
                              ->where([
                                  ['group_member.group_id', '=', $groupId],
                                  ['group_member.is_quit', '=', 0],
                              ])->orderBy('leader', 'desc')->get()->toArray();
        return $this->response->success('success', $members);
    }

    /**
     * 获取群组公告列表
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getGroupNotices() : ResponseInterface
    {

        $groupId = $this->request->input('group_id', 0);

        // 判断用户是否是群成员
        if (!Group::isMember($groupId, $this->uid())) {
            return $this->response->fail('非管理员禁止操作...');
        }

        $rows = UsersGroupNotice::leftJoin(User::newModelInstance()->getTable(), 'users.id', '=', 'group_notice.creator_id')
                                ->where([
                                    ['group_notice.group_id', '=', $groupId],
                                    ['group_notice.is_delete', '=', 0]
                                ])
                                ->orderBy('group_notice.is_top', 'desc')
                                ->orderBy('group_notice.updated_at', 'desc')
                                ->get([
                                    'group_notice.id',
                                    'group_notice.creator_id',
                                    'group_notice.title',
                                    'group_notice.content',
                                    'group_notice.is_top',
                                    'group_notice.is_confirm',
                                    'group_notice.confirm_users',
                                    'group_notice.created_at',
                                    'group_notice.updated_at',
                                    'users.avatar',
                                    'users.nickname',
                                ])->toArray();

        return $this->response->success('success', $rows);
    }

    /**
     * 创建/编辑群公告
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    public function editNotice() : ?ResponseInterface
    {
        $params    = $this->request->inputs(['group_id', 'notice_id', 'title', 'content', 'is_top', 'is_confirm']);
        $validator = $this->validationFactory->make($params, [
            'notice_id'  => 'required|integer',
            'group_id'   => 'required|integer',
            'title'      => 'required|max:50',
            'is_top'     => 'integer|in:0,1',
            'is_confirm' => 'integer|in:0,1',
            'content'    => 'required'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        // 判断用户是否是管理员
        if (!Group::isManager($this->uid(), $params['group_id'])) {
            return $this->response->fail('非管理员禁止操作...');
        }

        // 判断是否是新增数据
        if (empty($params['notice_id'])) {
            $result = UsersGroupNotice::create([
                'group_id'   => $params['group_id'],
                'creator_id' => $this->uid(),
                'title'      => $params['title'],
                'content'    => $params['content'],
                'is_top'     => $params['is_top'],
                'is_confirm' => $params['is_confirm'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                return $this->response->fail('添加群公告信息失败...');
            }

            // ... 推送群消息（预留）

            return $this->response->success('添加群公告信息成功...', []);
        }
    }

    /**
     * 删除群公告(软删除)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteNotice() : ResponseInterface
    {
        $params    = $this->request->inputs(['group_id', 'notice_id']);
        $validator = $this->validationFactory->make($params, [
            'group_id'  => 'required|integer',
            'notice_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 判断用户是否是管理员
        if (!Group::isManager($this->uid(), $params['group_id'])) {
            return $this->response->fail('非法操作...');
        }

        $result = UsersGroupNotice::where('id', $params['notice_id'])
                             ->where('group_id', $params['group_id'])
                             ->update([
                                 'is_delete'  => 1,
                                 'deleted_at' => date('Y-m-d H:i:s')
                             ]);

        return $result
            ? $this->response->success('公告删除成功...')
            : $this->response->fail('公告删除失败...');
    }
}

