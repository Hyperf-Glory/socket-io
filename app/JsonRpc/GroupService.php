<?php
declare(strict_types = 1);

namespace App\JsonRpc;

use App\Helper\ValidateHelper;
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

    /**
     * @param int   $uid
     * @param array $groupInfo
     * @param array $friendIds
     *
     * @return array|mixed
     */
    public function create(int $uid, array $groupInfo, $friendIds = [])
    {
        if (empty($uid) || empty($groupInfo)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        [$bool, $data] = $this->groupService->create($uid, $groupInfo, array_unique($friendIds));
        if ($bool) {
            /**
             * $data = ['record_id' => $result, 'group_id' => $group]
             */
            return ['code' => 1, 'data' => $data, 'msg' => '群聊创建成功...'];
        }
        return ['code' => 0, 'msg' => '创建群聊失败，请稍后再试...'];
    }

    /**
     * @param int $groupId
     * @param int $uid
     *
     * @return array|mixed
     */
    public function dismiss(int $groupId, int $uid)
    {
        if (empty($groupId) || empty($uid)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (!ValidateHelper::isInteger($groupId) || !ValidateHelper::isInteger($uid)) {
            return ['code' => 0, 'msg' => '参数错误...'];
        }
        $bool = $this->groupService->dismiss($groupId, $uid);
        if ($bool) {
            //TODO 推送群消息
        }
        return $bool ? ['code' => 1, 'msg' => '群聊已解散成功...'] : ['code' => 0, 'msg' => '群聊解散失败...'];
    }

    /**
     * @param int   $uid
     * @param int   $groupId
     * @param array $friendIds
     *
     * @return array|mixed
     */
    public function invite(int $uid, int $groupId, $friendIds = [])
    {
        if (empty($uid) || empty($groupId)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (!ValidateHelper::isInteger($groupId) || !ValidateHelper::isInteger($uid)) {
            return ['code' => 0, 'msg' => '参数错误...'];
        }
        [$bool, $record] = $this->groupService->invite($uid, $groupId, array_unique($friendIds));
        if ($bool) {
            return [
                'code' => 1,
                'msg'  => '好友已成功加入群聊...',
                'data' => [
                    'record_id' => $record
                ]
            ];
        }
        return [
            'code' => 0,
            'msg'  => '加入群聊失败...'
        ];
    }

    /**
     * @param int $uid
     * @param int $groupId
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function quit(int $uid, int $groupId)
    {
        if (empty($uid) || empty($groupId)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        if (!ValidateHelper::isInteger($groupId) || !ValidateHelper::isInteger($uid)) {
            return ['code' => 0, 'msg' => '参数错误...'];
        }
        [$bool, $record] = $this->groupService->quit($uid, $groupId);
        if ($bool) {
            return [
                'code' => 1,
                'msg'  => '已成功退出群聊...',
                'data' => [
                    'record_id' => $record
                ]
            ];
        }
        return [
            'code' => 0,
            'msg'  => '退出群聊失败...'
        ];
    }

    /**
     * @param int   $groupId
     * @param int   $uid
     * @param array $memberIds
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function removeMember(int $groupId, int $uid, array $memberIds)
    {
        if (empty($uid) || empty($groupId) || empty($memberIds)) {
            return ['code' => 0, 'msg' => '参数不正确...'];
        }
        //TODO 此处ValidateHelper::isIndexArray可能会验证失败
        if (!ValidateHelper::isInteger($groupId) || !ValidateHelper::isInteger($uid) || !ValidateHelper::isIndexArray($memberIds)) {
            return ['code' => 0, 'msg' => '参数错误...'];
        }
        [$bool, $record] = $this->groupService->removeMember($uid, $groupId, $memberIds);
        if ($bool) {
            return [
                'code' => 1,
                'msg'  => '群聊用户已被移除..',
                'data' => [
                    'record_id' => $record
                ]
            ];
        }
        return [
            'code' => 0,
            'msg'  => '群聊用户移除失败...'
        ];
    }
}
