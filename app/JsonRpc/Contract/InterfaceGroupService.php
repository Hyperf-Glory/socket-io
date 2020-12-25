<?php

declare(strict_types=1);
/**
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/Hyperf-Glory/socket-io
 */
namespace App\JsonRpc\Contract;

interface InterfaceGroupService
{
    /**
     * 创建群组.
     *
     * @param array $friendIds
     *
     * @return mixed
     */
    public function create(int $uid, array $groupInfo, $friendIds = []);

    /**
     * 解散群组.
     *
     * @return mixed
     */
    public function dismiss(int $groupId, int $uid);

    /**
     * 邀请加入群组.
     *
     * @param array $friendIds
     *
     * @return mixed
     */
    public function invite(int $uid, int $groupId, $friendIds = []);

    /**
     * 退出群组.
     *
     * @return mixed
     */
    public function quit(int $uid, int $groupId);

    /**
     *踢出群组(管理员特殊权限).
     *
     * @return mixed
     */
    public function removeMember(int $groupId, int $uid, array $memberIds);

    /**
     * 设置用户群名片.
     *
     * @return mixed
     */
    public function setGroupCard(int $uid, int $groupId, string $visitCard);

    /**
     * 获取用户可邀请加入群组的好友列表.
     *
     * @return mixed
     */
    public function getInviteFriends(int $uid, int $groupId);

    /**
     * 获取群组成员列表.
     *
     * @return mixed
     */
    public function getGroupMembers(int $groupId, int $uid);

    /**
     * 获取群组公告列表.
     *
     * @return mixed
     */
    public function getGroupNotices(int $uid, int $groupId);

    /**
     * 创建/编辑群公告.
     *
     * @return mixed
     */
    public function editNotice(int $uid, int $noticeid, int $groupId, string $title, string $content);

    /**
     * 删除群公告(软删除).
     *
     * @return mixed
     */
    public function deleteNotice(int $uid, int $groupId, int $noticeId);

    /**
     * 获取群信息接口.
     *
     * @return mixed
     */
    public function detail(int $uid, int $groupId);
}
