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
namespace App\Model;

/**
 * @property int $id
 * @property int $type
 * @property int $uid
 * @property int $friend_id
 * @property int $group_id
 * @property int $status
 * @property int $is_top
 * @property int $not_disturb
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UsersChatList extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_chat_list';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'type', 'uid', 'friend_id', 'group_id', 'status', 'is_top', 'not_disturb', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'type' => 'integer', 'uid' => 'integer', 'friend_id' => 'integer', 'group_id' => 'integer', 'status' => 'integer', 'is_top' => 'integer', 'not_disturb' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 删除聊天列表.
     *
     * @param int $uid 用户ID
     * @param int $id 聊天列表ID、好友ID或群聊ID
     * @param int $type ID类型 （1：聊天列表ID  2:好友ID  3:群聊ID）
     */
    public static function delItem(int $uid, int $id, $type = 1): bool
    {
        if ($type === 1) {
            return (bool) self::where('id', $id)->where('uid', $uid)->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        if ($type === 2) {
            return (bool) self::where('uid', $uid)->where('friend_id', $id)->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        return (bool) self::where('uid', $uid)->where('group_id', $id)->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * 创建聊天列表记录.
     *
     * @param int $user_id 用户ID
     * @param int $receive_id 接收者ID
     * @param int $type 创建类型 1:私聊  2:群聊
     */
    public static function addItem(int $user_id, int $receive_id, int $type): array
    {
        /**
         * @var self $result
         */
        $result = self::where('uid', $user_id)->where('type', $type)->where($type === 1 ? 'friend_id' : 'group_id', $receive_id)->first();
        if ($result) {
            $result->status = 1;
            $result->updated_at = date('Y-m-d H:i:s');
            $result->save();
            return ['id' => $result->id, 'type' => $result->type, 'friend_id' => $result->friend_id, 'group_id' => $result->group_id];
        }
        if (! ($result = self::create(['type' => $type, 'uid' => $user_id, 'status' => 1, 'friend_id' => $type === 1 ? $receive_id : 0, 'group_id' => $type === 2 ? $receive_id : 0, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]))) {
            return [];
        }
        return ['id' => $result->id, 'type' => $result->type, 'friend_id' => $result->friend_id, 'group_id' => $result->group_id];
    }

    /**
     * 聊天对话列表置顶操作.
     *
     * @param int $user_id 用户ID
     * @param int $list_id 对话列表ID
     * @param bool $is_top 是否置顶（true:是 false:否）
     */
    public static function topItem(int $user_id, int $list_id, $is_top = true): bool
    {
        return (bool) self::where('id', $list_id)->where('uid', $user_id)->update(['is_top' => $is_top ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * 设置消息免打扰.
     *
     * @param int $user_id 用户ID
     * @param int $receive_id 接收者ID
     * @param int $type 接收者类型（1:好友  2:群组）
     * @param int $not_disturb 是否免打扰
     */
    public static function notDisturbItem(int $user_id, int $receive_id, int $type, int $not_disturb): bool
    {
        /**
         * @var self $result
         */
        $result = self::where('uid', $user_id)->where($type === 1 ? 'friend_id' : 'group_id', $receive_id)->where('status', 1)->first(['id', 'not_disturb']);

        if (! $result || $not_disturb === $result->not_disturb) {
            return false;
        }
        return (bool) self::where('id', $result->id)->update(['not_disturb' => $not_disturb]);
    }
}
