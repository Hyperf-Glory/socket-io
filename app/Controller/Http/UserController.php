<?php
declare(strict_types = 1);

namespace App\Controller\Http;

use App\Controller\AbstractController;
use App\Service\UserFriendService;
use App\Service\UserService;
use Psr\Http\Message\ResponseInterface;

class UserController extends AbstractController
{
    private $service;

    private $friendService;

    public function __construct(UserService $service, UserFriendService $friendService)
    {
        $this->service       = $service;
        $this->friendService = $friendService;
        parent::__construct();
    }

    /**
     * 用户相关设置.
     */
    public function getUserSetting() : ResponseInterface
    {

    }

    /**
     * 获取好友申请未读数.
     */
    public function getApplyUnreadNum() : ResponseInterface
    {

    }

    /**
     * 获取我的信息.
     */
    public function getUserDetail() : ResponseInterface
    {

    }

    /**
     * 获取我的好友列表.
     */
    public function getUserFriends() : ResponseInterface
    {

    }

    /**
     * 修改我的密码
     */
    public function editUserPassword() : ResponseInterface
    {

    }

    public function getFriendApplyRecords() : ResponseInterface
    {

    }

    /**
     * 发送添加好友申请.
     */
    public function sendFriendApply() : ResponseInterface
    {

    }

    /**
     * 处理好友的申请.
     */
    public function handleFriendApply() : ResponseInterface
    {

    }

    /**
     * 删除好友申请记录.
     */
    public function deleteFriendApply() : ResponseInterface
    {

    }

    /**
     * 编辑好友备注信息.
     */
    public function editFriendRemark() : ResponseInterface
    {

    }

    /**
     * 获取指定用户信息.
     */
    public function searchUserInfo() : ResponseInterface
    {

    }

    /**
     * 获取用户群聊列表.
     */
    public function getUserGroups() : ResponseInterface
    {

    }

    /**
     * 更换用户手机号.
     */
    public function editUserMobile() : ResponseInterface
    {

    }

    /**
     * 修改手机号发送验证码
     */
    public function sendMobileCode() : ResponseInterface
    {

    }

    /**
     * 解除好友关系.
     */
    public function removeFriend() : ResponseInterface
    {

    }

    public function sendChangeEmailCode() : ResponseInterface
    {

    }

    public function editUserEmail() : ResponseInterface
    {

    }

    public function editAvatar() : ResponseInterface
    {

    }
}
