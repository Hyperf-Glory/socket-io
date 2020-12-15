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
namespace App\Controller;

use App\Cache\ApplyNumCache;
use App\Cache\FriendRemarkCache;
use App\Component\Mail;
use App\Component\Sms;
use App\Helper\ValidateHelper;
use App\Kernel\SocketIO;
use App\Model\Users;
use App\Model\UsersChatList;
use App\Model\UsersFriends;
use App\Service\UserFriendService;
use App\Service\UserService;
use Hyperf\Redis\RedisFactory;
use Psr\Http\Message\ResponseInterface;

class UserController extends AbstractController
{
    private $service;

    private $friendService;

    public function __construct(UserService $service, UserFriendService $friendService)
    {
        $this->service = $service;
        $this->friendService = $friendService;
        parent::__construct();
    }

    /**
     * 用户相关设置.
     */
    public function getUserSetting(): ResponseInterface
    {
        $info = $this->service->findById($this->uid(), ['id', 'nickname', 'avatar', 'motto', 'gender']);
        return $this->response->success('success', [
            'user_info' => [
                'uid' => $info->id,
                'nickname' => $info->nickname,
                'avatar' => $info->avatar,
                'motto' => $info->motto,
                'gender' => $info->gender,
            ],
            'setting' => [
                'theme_mode' => '',
                'theme_bag_img' => '',
                'theme_color' => '',
                'notify_cue_tone' => '',
                'keyboard_event_notify' => '',
            ],
        ]);
    }

    /**
     * 获取好友申请未读数.
     */
    public function getApplyUnreadNum(): ResponseInterface
    {
        return $this->response->success('success', [
            'unread_num' => (int) ApplyNumCache::get($this->uid()),
        ]);
    }

    /**
     * 获取我的信息.
     */
    public function getUserDetail(): ResponseInterface
    {
        $userInfo = $this->service->findById($this->uid(), ['mobile', 'nickname', 'avatar', 'motto', 'email', 'gender']);
        return $this->response->success('success', [
            'mobile' => $userInfo->mobile,
            'nickname' => $userInfo->nickname,
            'avatar' => $userInfo->avatar,
            'motto' => $userInfo->motto,
            'email' => $userInfo->email,
            'gender' => $userInfo->gender,
        ]);
    }

    /**
     * 获取我的好友列表.
     */
    public function getUserFriends(): ResponseInterface
    {
        $rows = UsersFriends::getUserFriends($this->uid());
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $cache = array_keys($redis->hGetAll(SocketIO::HASH_UID_TO_SID_PREFIX));

        foreach ($rows as $k => $row) {
            $rows[$k]['online'] = in_array($row['id'], $cache, true);
        }
        return $this->response->success('success', $rows);
    }

    /**
     * 编辑我的信息.
     */
    public function editUserDetail(): ResponseInterface
    {
        $params = ['nickname', 'avatar', 'motto', 'gender'];
        if (! $this->request->has($params) || ValidateHelper::isInteger($this->request->post('gender'))) {
            return $this->response->parmasError('参数错误!');
        }
        //TODO 编辑个人资料
        $bool = Users::where('id', $this->uid())->update($this->request->inputs($params));
        return $bool ? $this->response->success('个人信息修改成功') : $this->response->parmasError('个人信息修改失败');
    }

    /**
     * 修改我的密码
     */
    public function editUserPassword(): ResponseInterface
    {
        if (! $this->request->has(['old_password', 'new_password'])) {
            return $this->response->parmasError('参数错误!');
        }
        if (! ValidateHelper::checkPassword($this->request->input('new_password'))) {
            return $this->response->error('新密码格式错误(8~16位字母加数字)');
        }
        $info = $this->service->findById($this->uid(), ['id', 'password', 'mobile']);
        if (! $this->service->checkPassword($info->password, $this->request->input('password'))) {
            return $this->response->error('旧密码验证失败!');
        }
        $bool = $this->service->resetPassword($info->mobile, $this->request->input('new_password'));
        return $bool ? $this->response->success('密码修改成功...') : $this->response->parmasError('密码修改失败...');
    }

    /**
     * 获取我的好友申请记录.
     */
    public function getFriendApplyRecords(): ResponseInterface
    {
        $page = (int) $this->request->input('page', 1);
        $pageSize = (int) $this->request->input('page_size', 10);
        $data = $this->friendService->findApplyRecords($this->uid(), (int) $page, (int) $pageSize);
        ApplyNumCache::del($this->uid());
        return $this->response->success('success', $data);
    }

    /**
     * 发送添加好友申请.
     */
    public function sendFriendApply(): ResponseInterface
    {
        $friendId = (int) $this->request->post('friend_id');
        $remarks = $this->request->post('remarks', '');
        if (! ValidateHelper::isInteger($friendId)) {
            return $this->response->parmasError('参数错误!');
        }

        $bool = $this->friendService->addFriendApply($this->uid(), (int) $friendId, $remarks);
        if (! $bool) {
            return $this->response->error('发送好友申请失败...');
        }
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));

        //断对方是否在线。如果在线发送消息通知
        if ($redis->hGet(SocketIO::HASH_UID_TO_SID_PREFIX, (string) $friendId)) {
        }
        // 好友申请未读消息数自增
        ApplyNumCache::setInc((int) $friendId);
        return $this->response->success('发送好友申请成功...');
    }

    /**
     * 处理好友的申请.
     */
    public function handleFriendApply(): ResponseInterface
    {
        $applyId = (int) $this->request->post('apply_id');
        $remarks = $this->request->post('remarks', '');
        if (! ValidateHelper::isInteger($applyId)) {
            return $this->response->parmasError('参数错误!');
        }
        $bool = $this->friendService->handleFriendApply($this->uid(), (int) $applyId, $remarks);
        //判断是否是同意添加好友
        if ($bool) {
            //... 推送处理消息
        }
        return $bool ? $this->response->success('处理完成...') : $this->response->error('处理失败，请稍后再试...');
    }

    /**
     * 删除好友申请记录.
     */
    public function deleteFriendApply(): ResponseInterface
    {
        $applyId = (int) $this->request->post('apply_id');
        if (! ValidateHelper::isInteger($applyId)) {
            return $this->response->parmasError('参数错误!');
        }
        $bool = $this->friendService->delFriendApply($this->uid(), $applyId);
        return $bool ? $this->response->success('删除成功...') : $this->response->parmasError('删除失败...');
    }

    /**
     * 编辑好友备注信息.
     */
    public function editFriendRemark(): ResponseInterface
    {
        $friendId = (int) $this->request->post('friend_id');
        $remarks = $this->request->post('remarks', '');
        if (empty($remarks) || ! ValidateHelper::isInteger($friendId)) {
            return $this->response->parmasError('参数错误!');
        }
        $bool = $this->friendService->editFriendRemark($this->uid(), (int) $friendId, $remarks);
        if ($bool) {
            FriendRemarkCache::set($this->uid(), (int) $friendId, $remarks);
        }
        return $bool ? $this->response->success('备注修改成功...') : $this->response->error('备注修改失败，请稍后再试...');
    }

    /**
     * 获取指定用户信息.
     */
    public function searchUserInfo(): ResponseInterface
    {
        $uid = (int) $this->request->post('user_id', '');
        $mobile = $this->request->post('mobile', '');

        $where = [];
        if (ValidateHelper::isInteger($uid)) {
            $where['uid'] = $uid;
        } elseif (ValidateHelper::isPhone($mobile)) {
            $where['mobile'] = $mobile;
        } else {
            return $this->response->parmasError('参数错误!');
        }

        if ($data = $this->service->searchUserInfo($where, $this->uid())) {
            return $this->response->success('success', $data);
        }
        return $this->response->fail(303, 'success');
    }

    /**
     * 获取用户群聊列表.
     */
    public function getUserGroups(): ResponseInterface
    {
        $rows = $this->service->getUserChatGroups($this->uid());
        return $this->response->success('success', $rows);
    }

    /**
     * 更换用户手机号.
     */
    public function editUserMobile(): ResponseInterface
    {
        $sms_code = $this->request->post('sms_code', '');
        $mobile = $this->request->post('mobile', '');
        $password = $this->request->post('password', '');
        if (! ValidateHelper::isPhone($mobile)) {
            return $this->response->error('手机号格式不正确');
        }
        if (empty($sms_code)) {
            return $this->response->error('短信验证码不正确');
        }
        if (! di(Sms::class)->check('change_mobile', $mobile, $sms_code)) {
            return $this->response->error('验证码填写错误...');
        }
        if (! $this->service->checkPassword($password, Users::where('id', $this->uid())->value('password'))) {
            return $this->response->error('账号密码验证失败');
        }
        [$bool, $message] = $this->service->changeMobile($this->uid(), $mobile);
        if ($bool) {
            di(Sms::class)->delCode('change_mobile', $mobile);
        }
        return $bool ? $this->response->success('手机号更换成功') : $this->response->error(($message));
    }

    /**
     * 修改手机号发送验证码
     */
    public function sendMobileCode(): ResponseInterface
    {
        if (in_array($this->uid(), [2054, 2055], true)) {
            return $this->response->parmasError('测试账号不支持修改手机号');
        }

        $mobile = $this->request->post('mobile', '');
        if (! ValidateHelper::isPhone($mobile)) {
            return $this->response->parmasError('手机号格式不正确');
        }

        if (Users::where('mobile', $mobile)->exists()) {
            return $this->response->error('手机号已被他人注册');
        }

        $data = ['is_debug' => true];
        [$isTrue, $result] = di(Sms::class)->send('change_mobile', $mobile);
        if ($isTrue) {
            $data['sms_code'] = $result['data']['code'];
        }
        // ... 处理发送失败逻辑，当前默认发送成功

        return $this->response->success('验证码发送成功...', $data);
    }

    /**
     * 解除好友关系.
     */
    public function removeFriend(): ResponseInterface
    {
        $friendId = (int) $this->request->post('friend_id');
        if (! ValidateHelper::isInteger($this->uid())) {
            return $this->response->parmasError('参数错误!');
        }

        if (! $this->friendService->removeFriend($this->uid(), $friendId)) {
            return $this->response->error('解除好友失败...');
        }

        //删除好友会话列表
        UsersChatList::delItem($this->uid(), $friendId, 2);
        UsersChatList::delItem($friendId, $this->uid(), 2);

        return $this->response->success('success');
    }

    /**
     * //TODO 发送绑定邮箱的验证码
     */
    public function sendChangeEmailCode(): ResponseInterface
    {
        $email = $this->request->post('email');
        if (empty($email)) {
            return $this->response->parmasError('参数错误!');
        }
        $bool = di(Mail::class)->send(Mail::CHANGE_EMAIL, '绑定邮箱', $email);
        if ($bool) {
            return $this->response->success('验证码发送成功...');
        }
        return $this->response->error('验证码发送失败...');
    }

    /**
     * 修改用户邮箱接口.
     */
    public function editUserEmail(): ResponseInterface
    {
        $email = $this->request->post('email', '');
        $email_code = $this->request->post('email_code', '');
        $password = $this->request->post('password', '');
        if (empty($email) || empty($email_code) || empty($password)) {
            return $this->response->parmasError();
        }
        //TODO 验证邮箱
        $mail = di(Mail::class);
        if (! $mail->check(Mail::CHANGE_EMAIL, $email, $email_code)) {
            return $this->response->error('验证码填写错误...');
        }
        $upassword = Users::where('id', $this->uid())->value('password');
        if (! $this->service->checkPassword($password, $upassword)) {
            return $this->response->error('账号密码验证失败...');
        }
        $bool = Usesr::where('id', $this->uid())->update(['email' => $email]);
        if ($bool) {
            $mail->delCode(Mail::CHANGE_EMAIL, $email);
        }
        return $bool ? $this->response->success('邮箱设置成功...') : $this->response->error('邮箱设置失败...');
    }
}
