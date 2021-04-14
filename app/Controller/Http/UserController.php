<?php
declare(strict_types = 1);

namespace App\Controller\Http;

use App\Cache\ApplyNumCache;
use App\Cache\FriendRemarkCache;
use App\Component\Mail;
use App\Component\Sms;
use App\Controller\AbstractController;
use App\Helper\ValidateHelper;
use App\Model\User;
use App\Model\UsersChatList;
use App\Model\UsersFriend;
use App\Service\UserFriendService;
use App\Service\UserService;
use App\SocketIO\SocketIO;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\RedisFactory;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class UserController extends AbstractController
{
    private UserService $service;

    private UserFriendService $friendService;

    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(UserService $service, UserFriendService $friendService, ValidatorFactoryInterface $validationFactory)
    {
        $this->service           = $service;
        $this->friendService     = $friendService;
        $this->validationFactory = $validationFactory;
    }

    /**
     * 用户相关设置.
     */
    public function getUserSetting() : ResponseInterface
    {
        $info = $this->service->findById($this->uid(), ['id', 'nickname', 'avatar', 'motto', 'gender']);
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
            ],
        ]);
    }

    /**
     * 获取好友申请未读数.
     */
    public function getApplyUnreadNum() : ResponseInterface
    {
        return $this->response->success('success', [
            'unread_num' => (int)ApplyNumCache::get($this->uid()),
        ]);
    }

    /**
     * 获取我的信息.
     */
    public function getUserDetail() : ResponseInterface
    {
        $userInfo = $this->service->findById($this->uid(), ['mobile', 'nickname', 'avatar', 'motto', 'email', 'gender']);
        return $this->response->success('success', [
            'mobile'   => $userInfo->mobile,
            'nickname' => $userInfo->nickname,
            'avatar'   => $userInfo->avatar,
            'motto'    => $userInfo->motto,
            'email'    => $userInfo->email,
            'gender'   => $userInfo->gender,
        ]);
    }

    /**
     * 获取我的好友列表.
     */
    public function getUserFriends() : ResponseInterface
    {
        $rows  = UsersFriend::getUserFriends($this->uid());
        $redis = $this->container->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $cache = array_keys($redis->hGetAll(SocketIO::HASH_UID_TO_SID_PREFIX));

        foreach ($rows as $k => $row) {
            $rows[$k]['online'] = in_array($row['id'], $cache, true);
        }
        return $this->response->success('success', $rows);
    }

    /**
     * 编辑我的信息.
     */
    public function editUserDetail(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'nickname' => 'required',
                'avatar'   => 'required',
                'motto'    => 'required',
                'gender'   => 'required',
            ],
            [
                'nickname.required' => '昵称不能为空...',
                'avatar.required'   => '头像不能为空...',
                'motto.required'    => '座右铭不能为空...',
                'gender.required'   => '性别不能为空...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $bool = Users::where('id', $this->uid())->update($validator->validated());
        return $bool ? $this->response->success('个人信息修改成功...') : $this->response->parameterError('个人信息修改失败...');
    }

    /**
     * 修改我的密码
     */
    public function editUserPassword(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'old_password' => 'required',
                'new_password' => 'required'
            ],
            [
                'old_password.required' => '旧密码不能为空...',
                'new_password.required' => '新密码不能为空...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $info = $this->service->findById($this->uid(), ['id', 'password', 'mobile']);
        if (!$this->service->checkPassword($this->request->input('old_password'), $info->password)) {
            return $this->response->error('旧密码错误!');
        }
        $bool = $this->service->resetPassword($info->mobile, $request->input('new_password'));
        return $bool ? $this->response->success('密码修改成功...') : $this->response->parameterError('密码修改失败...');
    }

    /**
     * 获取我的好友申请记录.
     */
    public function getFriendApplyRecords(RequestInterface $request) : ResponseInterface
    {
        $page     = (int)$request->input('page', 1);
        $pageSize = (int)$request->input('page_size', 10);
        $data     = $this->friendService->findApplyRecords($this->uid(), $page, $pageSize);
        ApplyNumCache::del($this->uid());
        return $this->response->success('success', $data);
    }

    /**
     * 发送添加好友申请.
     */
    public function sendFriendApply(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'friend_id' => 'required',
                'remarks'   => 'required',
            ],
            [
                'friend_id.required' => '好友不能为空...',
                'remarks.required'   => '备注不能为空...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $bool = $this->friendService->addFriendApply($this->uid(), (int)$data['friend_id'], $data['remarks']);
        if (!$bool) {
            return $this->response->error('发送好友申请失败...');
        }
        $redis = $this->container->get(RedisFactory::class)->get(env('CLOUD_REDIS'));

        //断对方是否在线。如果在线发送消息通知
        if ($redis->hGet(SocketIO::HASH_UID_TO_SID_PREFIX, (string)$data['friend_id'])) {

        }
        // 好友申请未读消息数自增
        ApplyNumCache::setInc((int)$data['friend_id']);
        return $this->response->success('发送好友申请成功...');
    }

    /**
     * 处理好友的申请.
     */
    public function handleFriendApply(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'apply_id' => 'required',
                'remarks'  => 'required',
            ],
            [
                'apply_id.required' => '申请不能为空...',
                'remarks.required'  => '备注不能为空...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $bool = $this->friendService->handleFriendApply($this->uid(), (int)$data['apply_id'], $data['remarks']);
        //判断是否是同意添加好友
        if ($bool) {
            //... 推送处理消息
        }
        return $bool ? $this->response->success('处理完成...') : $this->response->error('处理失败，请稍后再试...');
    }

    /**
     * 删除好友申请记录.
     */
    public function deleteFriendApply(RequestInterface $request) : ResponseInterface
    {
        $applyId = (int)$request->post('apply_id');
        $bool    = $this->friendService->delFriendApply($this->uid(), $applyId);
        return $bool ? $this->response->success('删除成功...') : $this->response->parameterError('删除失败...');
    }

    /**
     * 编辑好友备注信息.
     */
    public function editFriendRemark(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'apply_id' => 'required',
                'remarks'  => 'required',
            ],
            [
                'apply_id.required' => '申请不能为空...',
                'remarks.required'  => '备注不能为空...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $bool = $this->friendService->editFriendRemark($this->uid(), (int)$data['friend_id'], $data['remarks']);
        if ($bool) {
            FriendRemarkCache::set($this->uid(), (int)$data['friend_id'], $data['remarks']);
        }
        return $bool ? $this->response->success('备注修改成功...') : $this->response->error('备注修改失败，请稍后再试...');
    }

    /**
     * 获取指定用户信息.
     */
    public function searchUserInfo(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'mobile' => 'required|mobile',
            ],
            [
                'mobile.required' => '手机号不能为空...',
                'mobile.mobile'   => '手机号格式不正确...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data            = $validator->validated();
        $where           = [];
        $where['mobile'] = $data['mobile'];
        if ($data = $this->service->searchUserInfo($where, $this->uid())) {
            return $this->response->success('success', $data);
        }
        return $this->response->error('success');
    }

    /**
     * 获取用户群聊列表.
     */
    public function getUserGroups() : ResponseInterface
    {
        $rows = $this->service->getUserChatGroups($this->uid());
        return $this->response->success('success', $rows);
    }

    /**
     * 更换用户手机号.
     */
    public function editUserMobile(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'mobile'   => 'required|mobile',
                'password' => 'required',
                'sms_code' => 'required|max:6',
            ],
            [
                'mobile.required'   => '手机号不能为空...',
                'mobile.mobile'     => '手机号格式不正确...',
                'password.required' => '密码不能为空...',
                'sms_code.required' => '验证码不正确...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        if (!$this->container->get(Sms::class)->check('change_mobile', $data['mobile'], $data['sms_code'])) {
            return $this->response->error('验证码填写错误...');
        }
        if (!$this->service->checkPassword($data['password'], Users::where('id', $this->uid())->value('password'))) {
            return $this->response->error('账号密码验证失败');
        }
        [$bool, $message] = $this->service->changeMobile($this->uid(), $data['mobile']);
        if ($bool) {
            $this->container->get(Sms::class)->delCode('change_mobile', $data['mobile']);
        }
        return $bool ? $this->response->success('手机号更换成功') : $this->response->error(($message));
    }

    /**
     * 修改手机号发送验证码
     */
    public function sendMobileCode() : ResponseInterface
    {
        if (in_array($this->uid(), [2054, 2055], true)) {
            return $this->response->parameterError('测试账号不支持修改手机号');
        }
        $mobile = $this->request->post('mobile', '');
        if (!ValidateHelper::isPhone($mobile)) {
            return $this->response->parameterError('手机号格式不正确');
        }

        if (User::where('mobile', $mobile)->exists()) {
            return $this->response->error('手机号已被他人注册');
        }
        $data = ['is_debug' => true];
        [$isTrue, $result] = $this->container->get(Sms::class)->send('change_mobile', $mobile);
        if ($isTrue) {
            $data['sms_code'] = $result['data']['code'];
        }
        // ... 处理发送失败逻辑，当前默认发送成功

        return $this->response->success('验证码发送成功...', $data);
    }

    /**
     * 解除好友关系.
     */
    public function removeFriend(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'friend_id' => 'required|integer',
            ],
            [
                'friend_id.required' => '参数不能为空...',
                'friend_id.integer'  => '参数不正确...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        //删除好友会话列表
        UsersChatList::delItem($this->uid(), $data['friend_id'], 2);
        UsersChatList::delItem($data['friend_id'], $this->uid(), 2);

        return $this->response->success('success');
    }

    /**
     * 发送修改邮箱验证码
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendChangeEmailCode(RequestInterface $request) : ResponseInterface
    {
        $email = $this->request->post('email');
        if (empty($email)) {
            return $this->response->parmasError('参数错误!');
        }
        $bool = $this->container->get(Mail::class)->send(Mail::CHANGE_EMAIL, '绑定邮箱', $email);
        if ($bool) {
            return $this->response->success('验证码发送成功...');
        }
        return $this->response->error('验证码发送失败...');
    }

    /**
     * 修改用户邮箱
     *
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function editUserEmail(RequestInterface $request) : ResponseInterface
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'email'      => 'required|email',
                'email_code' => 'required',
                'password'   => 'required',
            ],
            [
                'email.required'      => '邮箱不能为空...',
                'email.email'         => '邮箱不正确...',
                'email_code.required' => '邮箱验证码不能为空...',
                'password.required'   => '密码不能为空...',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $mail = $this->container->get(Mail::class);
        if (!$mail->check(Mail::CHANGE_EMAIL, $data['email'], $data['email_code'])) {
            return $this->response->error('验证码填写错误...');
        }
        $oldPassword = User::where('id', $this->uid())->value('password');
        if (!$this->service->checkPassword($data['password'], $oldPassword)) {
            return $this->response->error('账号密码验证失败...');
        }
        $bool = User::where('id', $this->uid())->update(['email' => $data['email']]);
        if ($bool) {
            $mail->delCode(Mail::CHANGE_EMAIL, $data['email']);
        }
        return $bool ? $this->response->success('邮箱设置成功...') : $this->response->error('邮箱设置失败...');
    }

    /**
     * 修改头像
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function editAvatar() : ResponseInterface
    {
        $avatar = $this->request->post('avatar');
        if (empty($avatar)) {
            return $this->response->parameterError();
        }

        $isTrue = User::where('id', $this->uid())->update(['avatar' => $avatar]);

        return $isTrue ? $this->response->success('头像修改成功') : $this->response->error('头像修改失败');
    }
}
