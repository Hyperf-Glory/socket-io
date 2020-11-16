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
namespace App\Model;

/**
 * @property int            $id
 * @property int            $type
 * @property int            $uid
 * @property int            $friend_id
 * @property int            $group_id
 * @property int            $status
 * @property int            $is_top
 * @property int            $not_disturb
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
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'type' => 'integer', 'uid' => 'integer', 'friend_id' => 'integer', 'group_id' => 'integer', 'status' => 'integer', 'is_top' => 'integer', 'not_disturb' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 删除聊天列表
     *
     * @param int $uid  用户ID
     * @param int $id   聊天列表ID、好友ID或群聊ID
     * @param int $type ID类型 （1：聊天列表ID  2:好友ID  3:群聊ID）
     *
     * @return bool
     */
    public static function delItem(int $uid, int $id, $type = 1)
    {
        if ($type == 1) {
            return (bool)self::where('id', $id)->where('uid', $uid)->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            if ($type == 2) {
                return (bool)self::where('uid', $uid)->where('friend_id', $id)->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
            } else {
                return (bool)self::where('uid', $uid)->where('group_id', $id)->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }
    }
}
