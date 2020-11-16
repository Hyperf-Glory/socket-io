<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Cache\ApplyNumCache;
use App\Helper\ValidateHelper;
use App\Kernel\SocketIO;
use App\Model\Users;
use App\Model\UsersFriends;
use App\Service\UserFriendService;
use App\Service\UserService;
use Hyperf\Redis\RedisFactory;

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
     * 用户相关设置
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getUserSetting()
    {
        $user = $this->request->getAttribute('user');
        $info = $this->service->findById($user['id'], ['id', 'nickname', 'avatar', 'motto', 'gender']);
        return $this->response->success('success', [
            'user_info' => [
                'uid'      => $info->id,
                'nickname' => $info->nickname,
                'avatar'   => $info->avatar,
                'motto'    => $info->motto,
                'gender'   => $info->gender,
            ],
            'setting'   => [
                'theme_mode'            => '',
                'theme_bag_img'         => '',
                'theme_color'           => '',
                'notify_cue_tone'       => '',
                'keyboard_event_notify' => '',
            ]
        ]);
    }

    /**
     * 获取好友申请未读数
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getApplyUnreadNum()
    {
        $user = $this->request->getAttribute('user');
        return $this->response->success('success', [
            'unread_num' => ApplyNumCache::get($user['id'])
        ]);
    }

    /**
     * 获取我的信息
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getUserDetail()
    {
        $user     = $this->request->getAttribute('user');
        $userInfo = $this->service->findById($user['id'], ['mobile', 'nickname', 'avatar', 'motto', 'email', 'gender']);
        return $this->response->success('success', [
            'mobile'   => $userInfo->mobile,
            'nickname' => $userInfo->nickname,
            'avatar'   => $userInfo->avatar,
            'motto'    => $userInfo->motto,
            'email'    => $userInfo->email,
            'gender'   => $userInfo->gender
        ]);
    }

    /**
     * 获取我的好友列表
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getUserFriends()
    {
        $user  = $this->request->getAttribute('user');
        $rows  = UsersFriends::getUserFriends($user['id']);
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $cache = array_keys($redis->hGetAll(SocketIO::HASH_UID_TO_FD_PREFIX));

        foreach ($rows as $k => $row) {
            $rows[$k]['online'] = in_array($row['id'], $cache) ? true : false;
        }
        return $this->response->success('success', $rows);
    }

    /**
     * 编辑我的信息
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function editUserDetail()
    {
        $user   = $this->request->getAttribute('user');
        $params = ['nickname', 'avatar', 'motto', 'gender'];
        if (!$this->request->has($params) || ValidateHelper::isInteger($this->request->post('gender'))) {
            return $this->response->fail(301, '参数错误!');
        }
        //TODO 编辑个人资料
        //待驾照拿到之后继续更新
        $bool = Users::where('id', $user['id'])->update($this->request->inputs($params));
        return $bool ? $this->response->success('个人信息修改成功') : $this->response->fail(301, '个人信息修改失败');
    }

    /**
     * 修改我的密码
     *
     * @return \Psr\Http\Message\ResponseInterface
     */

    public function editUserPassword()
    {
        $user = $this->request->getAttribute('user');
        if (!$this->request->has(['old_password', 'new_password'])) {
            return $this->response->fail(301, '参数错误!');
        }
        if (!ValidateHelper::checkPassword($this->request->input('new_password'))) {
            return $this->response->fail(301, '新密码格式错误(8~16位字母加数字)');
        }
        $info = $this->service->findById($user['id'], ['id', 'password', 'mobile']);
        if (!$this->service->checkPassword($info->password, $this->request->input('password'))) {
            return $this->response->fail(301, '旧密码验证失败!');
        }
        $bool = $this->service->resetPassword($info->mobile, $this->request->input('new_password'));
        return $bool ? $this->response->success('密码修改成功...') : $this->response->fail(301, '密码修改失败...');
    }

    /**
     * 获取我的好友申请记录
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getFriendApplyRecords()
    {
        $page     = $this->request->input('page', 1);
        $pageSize = $this->request->input('page_size', 10);
        $user     = $this->request->getAttribute('user');
        $data     = $this->friendService->findApplyRecords($user['id'], $page, $pageSize);
        ApplyNumCache::del($user['id']);
        return $this->response->success('success', $data);
    }

    /**
     *
     * 发送添加好友申请
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendFriendApply()
    {
        $friendId = $this->request->post('friend_id');
        $remarks  = $this->request->post('remarks', '');
        $user     = $this->request->getAttribute('user');
        if (!ValidateHelper::isInteger($friendId)) {
            return $this->response->fail(301, '参数错误!');
        }

        $bool = $this->friendService->addFriendApply($user['id'], $friendId, $remarks);
        if (!$bool) {
            return $this->response->fail(301, '发送好友申请失败...');
        }
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));

        //判断对方是否在线。如果在线发送消息通知
        if ($redis->hGet(SocketIO::HASH_UID_TO_FD_PREFIX, (string)$friendId)) {

        }
        // 好友申请未读消息数自增
        ApplyNumCache::setInc($friendId);
        return $this->response->success('发送好友申请成功...');
    }

    /**
     * 处理好友的申请
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handleFriendApply()
    {
        $applyId = $this->request->post('apply_id');
        $remarks = $this->request->post('remarks', '');
        $user    = $this->request->getAttribute('user');
        if (!ValidateHelper::isInteger($applyId)) {
            return $this->response->fail(301, '参数错误!');
        }
        $bool = $this->friendService->handleFriendApply($user['id'], $applyId, $remarks);
        //判断是否是同意添加好友
        if ($bool) {
            //... 推送处理消息
        }
        return $bool ? $this->response->success('处理完成...') : $this->response->fail(301, '处理失败，请稍后再试...');
    }

    /**
     * 删除好友申请记录
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteFriendApply()
    {
        $applyId = $this->request->post('apply_id');
        $user    = $this->request->getAttribute('user');
        if (!ValidateHelper::isInteger($applyId)) {
            return $this->response->fail(301, '参数错误!');
        }
        $bool = $this->friendService->delFriendApply($user['id'], $applyId);
        return $bool ? $this->response->success('删除成功...') : $this->response->fail(301, '删除失败...');
    }



}
