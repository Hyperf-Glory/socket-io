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

    /**
     * 设置用户群名片
     *
     * @param int    $uid
     * @param int    $groupId
     * @param string $visitCard
     *
     * @return mixed
     */
    public function setGroupCard(int $uid,int $groupId,string $visitCard);

    /**
     * 获取用户可邀请加入群组的好友列表
     *
     * @param int $uid
     * @param int $groupId
     *
     * @return mixed
     */
    public function getInviteFriends(int $uid,int $groupId);

    /**
     * 获取群组成员列表
     *
     * @param int $groupId
     *
     * @param int $uid
     *
     * @return mixed
     */
    public function getGroupMembers(int $groupId,int $uid);

    /**
     * 获取群组公告列表
     *
     * @param int $uid
     * @param int $groupId
     *
     * @return mixed
     */
    public function getGroupNotices(int $uid,int $groupId);

    /**
     * 创建/编辑群公告
     *
     * @param int    $uid
     * @param int    $noticeid
     * @param int    $groupId
     * @param string $title
     * @param string $content
     *
     * @return mixed
     */
    public function editNotice(int $uid,int $noticeid,int $groupId,string $title,string $content);

    /**
     * 删除群公告(软删除)
     *
     * @param int $uid
     * @param int $groupId
     * @param int $noticeId
     *
     * @return mixed
     */
    public function deleteNotice(int $uid,int $groupId,int $noticeId);
}
