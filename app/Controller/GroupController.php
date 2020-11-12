<?php
declare(strict_types = 1);

namespace App\Controller;

use App\JsonRpc\Contract\InterfaceGroupService;
use App\JsonRpc\Contract\InterfaceUserService;

class GroupController extends AbstractController
{
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
