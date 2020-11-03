<?php

declare(strict_types = 1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\JsonRpc;

use App\Constants\User;
use App\JsonRpc\Contract\InterfaceUserService;
use App\Model\User as UserModel;
use App\Service\UserService as UserSer;
use App\Task\CloudTask;
use Hyperf\Logger\LoggerFactory;
use Hyperf\RpcServer\Annotation\RpcService;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

/**
 * Class Cloud.
 * @RpcService(name="UserService", protocol="jsonrpc-tcp-length-check", server="jsonrpc", publishTo="consul")
 */
class UserService implements InterfaceUserService
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \App\Service\UserService
     */
    private $userService;

    public function __construct(ContainerInterface $container, UserSer $userService)
    {
        $this->container   = $container;
        $this->logger      = $container->get(LoggerFactory::class)->get();
        $this->userService = $userService;
    }

    public function register(string $mobile, string $password, string $smsCode, string $nickname)
    {

    }

    public function login(string $mobile, string $password)
    {

    }

    public function logout(string $token)
    {

    }

    public function sendVerifyCode(string $mobile, string $type = User::REGISTER)
    {

    }

    public function forgetPassword(string $mobile, string $smsCode, string $password)
    {

    }

    public function get(int $uid) : ?array
    {
        try {
            $user = $this->userService->get($uid);
            if ($user) {
                return [
                    'id'       => $user->id,
                    'nickname' => $user->nickname,
                    'avatar'   => $user->avatar
                ];
            }
            return null;
        } catch (\Throwable $throwable) {
            $this->logger->error(sprintf('json-rpc UserService Error getting user[%s] information', $uid));
        }
        return null;
    }
}
