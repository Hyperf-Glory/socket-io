<?php
declare(strict_types = 1);

namespace App\JsonRpc;

use App\JsonRpc\Contract\InterfaceGroupService;
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
