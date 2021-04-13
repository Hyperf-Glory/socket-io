<?php

declare(strict_types = 1);
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
namespace App\Service;

use App\Model\ChatRecordsFile;
use App\Model\EmoticonDetail;
use App\Model\Group;
use App\Model\GroupMember;
use App\Model\UsersEmoticon;
use App\Model\UsersGroup;

/**
 * 表情服务层
 *
 * Class EmoticonService
 */
class EmoticonService
{
    /**
     * 安装系统表情包.
     *
     * @param int $uid         用户ID
     * @param int $emoticon_id 表情包ID
     *
     * @return bool
     */
    public function installSysEmoticon(int $uid, int $emoticon_id) : bool
    {
        /**
         * @var UsersEmoticon $info
         */
        $info = UsersEmoticon::select(['id', 'user_id', 'emoticon_ids'])->where('user_id', $uid)->first();
        if (!$info) {
            return (bool)UsersEmoticon::create(['user_id' => $uid, 'emoticon_ids' => $emoticon_id]);
        }

        $emoticon_ids = $info->emoticon_ids;
        if (in_array($emoticon_id, $emoticon_ids, true)) {
            return true;
        }

        $emoticon_ids = (array)$emoticon_id;
        return (bool)UsersEmoticon::where('user_id', $uid)->update(['emoticon_ids' => implode(',', $emoticon_ids)]);
    }

    /**
     * 移除已安装的系统表情包.
     *
     * @param int $uid         用户ID
     * @param int $emoticon_id 表情包ID
     */
    public function removeSysEmoticon(int $uid, int $emoticon_id) : bool
    {
        /**
         * @var UsersEmoticon $info
         */
        $info = UsersEmoticon::select(['id', 'user_id', 'emoticon_ids'])->where('user_id', $uid)->first();
        if (!$info || !in_array($emoticon_id, $info->emoticon_ids, true)) {
            return false;
        }

        $emoticon_ids = $info->emoticon_ids;
        foreach ($emoticon_ids as $k => $id) {
            if ($id === $emoticon_id) {
                unset($emoticon_ids[$k]);
            }
        }

        if (count($info->emoticon_ids) === count($emoticon_ids)) {
            return false;
        }

        return (bool)UsersEmoticon::where('user_id', $uid)->update(['emoticon_ids' => implode(',', $emoticon_ids)]);
    }

    /**
     * 获取用户安装的表情ID.
     *
     * @param int $uid 用户ID
     */
    public function getInstallIds(int $uid) : array
    {
        return UsersEmoticon::where('user_id', $uid)->value('emoticon_ids') ?? [];
    }

    /**
     * 收藏聊天图片.
     *
     * @param int $uid       用户ID
     * @param int $record_id 聊天消息ID
     */
    public function collect(int $uid, int $record_id) : array
    {
        /**
         * @var \App\Model\ChatRecord $result
         */
        $result = ChatRecords::where([
            ['id', '=', $record_id],
            ['msg_type', '=', 2],
            ['is_revoke', '=', 0],
        ])->first(['id', 'source', 'msg_type', 'user_id', 'receive_id', 'is_revoke']);

        if (!$result) {
            return [false, []];
        }

        if ($result->source === 1) {
            if ($result->user_id !== $uid && $result->receive_id !== $uid) {
                return [false, []];
            }
        } elseif (!Group::isMember($result->receive_id, $uid)) {
            return [false, []];
        }

        /**
         * @var ChatRecordsFile $fileInfo
         */
        $fileInfo = ChatRecordsFile::where('record_id', $result->id)->where('file_type', 1)->first([
            'file_suffix',
            'file_size',
            'save_dir',
        ]);

        if (!$fileInfo) {
            return [false, []];
        }

        $result = EmoticonDetail::where('user_id', $uid)->where('url', $fileInfo->save_dir)->first();
        if ($result) {
            return [false, []];
        }

        $res = EmoticonDetail::create([
            'user_id'     => $uid,
            'url'         => $fileInfo->save_dir,
            'file_suffix' => $fileInfo->file_suffix,
            'file_size'   => $fileInfo->file_size,
            'created_at'  => time(),
        ]);

        return $res ? [true, ['media_id' => $res->id, 'src' => get_media_url($res->url)]] : [false, []];
    }

    /**
     * 移除收藏的表情包.
     *
     * @param int   $uid 用户ID
     * @param array $ids 表情包详情ID
     *
     * @return int
     * @throws \Exception
     */
    public function deleteCollect(int $uid, array $ids) : int
    {
        return EmoticonDetail::whereIn('id', $ids)->where('user_id', $uid)->delete();
    }

    /**
     * 获取表情包列表.
     *
     * @param array $where
     *
     * @return array
     */
    public function getDetailsAll(array $where = []) : array
    {
        $list = EmoticonDetail::where($where)->get(['id as media_id', 'url as src'])->toArray();

        foreach ($list as $k => $value) {
            $list[$k]['src'] = get_media_url($value['src']);
        }

        return $list;
    }
}
