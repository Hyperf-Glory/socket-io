<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Component\Proxy;
use App\JsonRpc\Contract\InterfaceGroupService;
use App\JsonRpc\Contract\InterfaceUserService;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\Utils\Coroutine;

class GroupController extends AbstractController
{
    /**
     *创建群聊
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create()
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
        if (isset($ret['code']) && $ret['code'] == 1) {
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

    public function detail()
    {

    }

    public function editDetail()
    {

    }

    public function invite()
    {

    }

    public function removeMembers()
    {

    }

    public function dismiss()
    {

    }

    public function secede()
    {

    }

    public function setGroupCard()
    {

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
