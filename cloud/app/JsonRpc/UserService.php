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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger    = $container->get(LoggerFactory::class)->get();
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
}
