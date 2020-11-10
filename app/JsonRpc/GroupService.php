<?php
declare(strict_types = 1);

namespace App\JsonRpc;

use App\JsonRpc\Contract\InterfaceGroupService;
use App\Service\UserService as UserSer;
use Hyperf\Logger\LoggerFactory;
use Hyperf\RpcServer\Annotation\RpcService;
use Phper666\JWTAuth\JWT;
use Psr\Container\ContainerInterface;

/**
 * Class GroupService
 * @package App\JsonRpc
 * @RpcService(name="GroupService", protocol="jsonrpc-tcp-length-check", server="jsonrpc", publishTo="consul")
 */
class GroupService implements InterfaceGroupService
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

    /**
     * @var \App\Service\GroupService
     */
    private $groupService;

    /**
     *
     * @var \Phper666\JWTAuth\JWT
     */
    protected $jwt;

    public function __construct(ContainerInterface $container, UserSer $userService, JWT $jwt, \App\Service\GroupService $groupService)
    {
        $this->container    = $container;
        $this->logger       = $container->get(LoggerFactory::class)->get();
        $this->userService  = $userService;
        $this->groupService = $groupService;
        $this->jwt          = $jwt;
    }

    public function create(int $uid, array $groupInfo, $friendIds = [])
    {
        if (empty($uid) || empty($groupInfo)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        [$isTrue, $data] = $this->groupService->create($uid,$groupInfo, array_unique($friendIds));
        if($isTrue){

        }
    }

    public function dismiss(int $groupId, int $uid)
    {

    }

    public function invite(int $uid, int $groupId, $friendIds = [])
    {

    }

    public function quit(int $uid, int $groupId)
    {

    }

    public function removeMember(int $groupId, int $uid, array $memberIds)
    {

    }
}
