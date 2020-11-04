<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Component\Sms;
use App\Helper\ValidateHelper;
use App\JsonRpc\Contract\InterfaceUserService;

class AuthController extends AbstractController
{
    /**
     * 注册接口
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
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

    /**
     * 登录
     * @return \Psr\Http\Message\ResponseInterface
     */
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

    /**
     * 退出登录
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function logout()
    {
        $token   = $this->request->getHeaderLine('Authorization') ?? '';
        $rpcUser = $this->container->get(InterfaceUserService::class);
        $rpcUser->logout($token);
        return $this->response->success('退出成功!');
    }

    /**
     * 发送验证码
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
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

    /**
     * 忘记密码
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function forgetPassword()
    {
        $mobile   = $this->request->post('mobile', '');
        $code     = $this->request->post('sms_code', '');
        $password = $this->request->post('password', '');
        $rpcUser  = $this->container->get(InterfaceUserService::class);
        $data     = $rpcUser->forgetPassword($mobile, $code, $password);
        if (isset($data['code']) && $data['code'] == 1) {
            return $this->response->success($data['msg']);
        }
        return $this->response->fail(301, '重置密码失败...');
    }
}
