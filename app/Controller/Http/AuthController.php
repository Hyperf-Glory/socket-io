<?php
declare(strict_types = 1);
namespace App\Controller\Http;

use App\Component\Sms;
use App\Controller\AbstractController;
use App\Event\LoginAfterEvent;
use App\JsonRpc\Contract\InterfaceUserService;
use App\Request\LoginRequest;
use App\Request\RegisterRequest;
use App\Request\SmsCodeRequest;
use Psr\Http\Message\ResponseInterface;

class AuthController extends AbstractController
{

    /**
     * 注册
     *
     * @param \App\Request\RegisterRequest $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function register(RegisterRequest $request) : ResponseInterface
    {
        $data     = $request->validated();
        $response = $this->container->get(InterfaceUserService::class)
                                    ->register($data['mobile'], $data['password'], $data['sms_code']);
        if ($response->isSuccess()) {
            return $this->response->success('账号注册成功...');
        }
        return $this->response->fail($response->getMessage());
    }

    /**
     * 登录
     *
     * @param \App\Request\LoginRequest $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function login(LoginRequest $request) : ResponseInterface
    {
        $data     = $request->validated();
        $response = $this->container->get(InterfaceUserService::class)
                                    ->login($data['mobile'], $data['password']);
        if ($response->isSuccess()) {
            //login log
            $this->eventDispatcher->dispatch(new LoginAfterEvent($this->uid(), getClientIp()));
            return $this->response->success('登录成功...', [
                'authorize' => $response->getData()['authorize'],
                'userInfo'  => $response->getData()['user_info'],
            ]);
        }
        return $this->response->fail($response->getMessage() ?? '登录失败...');
    }

    /**
     * 退出
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function logout() : ResponseInterface
    {
        $token = $this->request->getHeaderLine('Authorization') ?? '';
        $this->container->get(InterfaceUserService::class)->logout($token);
        return $this->response->success('退出成功...');
    }

    /**
     * 发送验证码
     *
     * @param \App\Request\SmsCodeRequest $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendVerifyCode(SmsCodeRequest $request) : ResponseInterface
    {
        $data = $request->validated();
        if (!$this->container->get(Sms::class)->isUsages($data['type'])) {
            return $this->response->error('验证码发送失败...');
        }
        $response = $this->container->get(InterfaceUserService::class)
                                    ->sendVerifyCode($data['mobile'], $data['type']);
        if ($response->isSuccess()) {
            return $this->response->success('验证码发送成功!', $response->getData());
        }
        return $this->response->error($response->getMessage());
    }

    /**
     *重置密码
     *
     * @param \App\Request\RegisterRequest $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function forgetPassword(RegisterRequest $request) : ResponseInterface
    {
        $data     = $request->validated();
        $response = $this->container->get(InterfaceUserService::class)
                                    ->forgetPassword($data['mobile'], $data['code'], $data['password']);
        if ($response->isSuccess()) {
            return $this->response->success($response->getMessage());
        }
        return $this->response->error('重置密码失败...');
    }
}

