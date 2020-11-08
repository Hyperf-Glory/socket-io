<?php
declare(strict_types = 1);

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * Class GroupService
 * @package App\JsonRpc
 * @RpcService(name="GroupService", protocol="jsonrpc-tcp-length-check", server="jsonrpc", publishTo="consul")
 */
class GroupService implements InterfaceGroupService
{

    public function create(int $uid, array $groupInfo, $friendIds = [])
    {
        // TODO: Implement create() method.
    }

    public function dismiss(int $groupId, int $uid)
    {
        // TODO: Implement dismiss() method.
    }

    public function invite(int $uid, int $groupId, $friendIds = [])
    {
        // TODO: Implement invite() method.
    }

    public function quit(int $uid, int $groupId)
    {
        // TODO: Implement quit() method.
    }

    public function removeMember(int $groupId, int $uid, array $memberIds)
    {
        // TODO: Implement removeMember() method.
    }
}
