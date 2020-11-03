<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Component\Sms;
use App\Helper\ValidateHelper;
use App\JsonRpc\Contract\InterfaceUserService;

class AuthController extends AbstractController
{
    public function register()
    {
        $params = $this->request->all();
        if (!ValidateHelper::isPhone($params['mobile'])) {
            return $this->response->fail(301, '手机号格式不正确...');
        }
        $rpcUser = $this->container->get(InterfaceUserService::class);
        $ret     = $rpcUser->register(
            $params['mobile'],
            $params['password'],
            $params['sms_code'],
            strip_tags($params['nickname'])
        );
        if (isset($ret['code']) && $ret['code'] == 1) {
            return $this->response->success('账号注册成功!');
        }
        return $this->response->fail(301, $ret['msg']);
    }

    public function login()
    {
        $params = $this->request->all();
        if (!ValidateHelper::isPhone($params['mobile'])) {
            return $this->response->fail(301, '手机号格式不正确...');
        }
        $rpcUser = $this->container->get(InterfaceUserService::class);
        $ret     = $rpcUser->login($params['mobile'], $params['password']);

        if (isset($ret['code']) && $ret['code'] == 1) {
            return $this->response->success('登录成功!', [
                'authorize' => $ret['authorize'],
                'userInfo'  => $ret['user_info']
            ]);
        }
        return $this->response->fail(301, $ret['msg'] ?? '登录失败...');
    }

    public function logout()
    {

    }

    public function sendVerifyCode()
    {
        $mobile = $this->request->post('mobile', '');
        $type   = $this->request->post('type', '');
        if (!di(Sms::class)->isUsages($type)) {
            return $this->response->fail(301, '验证码发送失败...');
        }
        if (!ValidateHelper::isPhone($mobile)) {
            return $this->response->fail(301, '手机号格式不正确...');
        }

        $rpcUser = $this->container->get(InterfaceUserService::class);
        $data    = $rpcUser->sendVerifyCode($mobile, $type);
        $data    = array_merge(['is_debug' => true], $data);
        if (isset($data['code']) && $data['code'] == 1) {
            return $this->response->success('验证码发送成功!', $data);
        }
        return $this->response->fail(301, $data['msg']);
    }

    public function forgetPassword()
    {

    }
}
