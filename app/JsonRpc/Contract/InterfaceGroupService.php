<?php
declare(strict_types = 1);

namespace App\JsonRpc\Contract;

interface InterfaceGroupService
{
    /**
     * 创建群组
     *
     * @param int   $uid
     * @param array $groupInfo
     * @param array $friendIds
     *
     * @return mixed
     */
    public function create(int $uid, array $groupInfo, $friendIds = []);

    /**
     * 解散群组
     *
     * @param int $groupId
     * @param int $uid
     *
     * @return mixed
     */
    public function dismiss(int $groupId,int $uid);

    /**
     * 邀请加入群组
     *
     * @param int   $uid
     * @param int   $groupId
     * @param array $friendIds
     *
     * @return mixed
     */
    public function invite(int $uid, int $groupId, $friendIds = []);

    /**
     * 退出群组
     * @param int $uid
     * @param int $groupId
     *
     * @return mixed
     */
    public function quit(int $uid, int $groupId);

    /**
     *踢出群组(管理员特殊权限)
     *
     * @param int   $groupId
     * @param int   $uid
     * @param array $memberIds
     *
     * @return mixed
     */
    public function removeMember(int $groupId, int $uid, array $memberIds);

}
