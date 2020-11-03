<?php
declare(strict_types = 1);

namespace App\JsonRpc\Client;

use App\Constants\User;
use App\JsonRpc\Contract\InterfaceUserService;
use Hyperf\RpcClient\AbstractServiceClient;

class UserClient extends AbstractServiceClient implements InterfaceUserService
{

    /**
     * 定义对应服务提供者的服务名称
     * @var string
     */
    protected $serviceName = 'UserService';

    /**
     * 定义对应服务提供者的服务协议
     * @var string
     */
    protected $protocol = 'jsonrpc';

    public function register(string $mobile, string $password, string $smsCode, string $nickname)
    {
    }

    public function login(string $mobile, string $password)
    {
    }

    public function logout(string $token)
    {
        // TODO: Implement logout() method.
    }

    public function sendVerifyCode(string $mobile, string $type = User::REGISTER)
    {
        // TODO: Implement sendVerifyCode() method.
    }

    public function forgetPassword(string $mobile, string $smsCode, string $password)
    {
        // TODO: Implement forgetPassword() method.
    }

    public function get(int $uid) : ?array
    {
        return $this->__request(__FUNCTION__, compact('uid'));
    }
}
