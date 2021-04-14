<?php
declare(strict_types = 1);

namespace App\Controller\Http;

use App\Component\Mail;
use App\Component\Sms;
use App\Controller\AbstractController;
use App\Helper\ValidateHelper;
use App\Model\User;
use App\Service\UserFriendService;
use App\Service\UserService;
use Hyperf\HttpServer\Contract\RequestInterface;
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

    /**
     * 通过手机号查找用户
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
}
