<?php

declare (strict_types=1);
namespace App\Model;

use Hyperf\DbConnection\Db;
/**
 * @property int $id 关系ID
 * @property int $user1 用户1(user1 一定比 user2 小)
 * @property int $user2 用户2(user1 一定比 user2 小)
 * @property string $user1_remark 好友备注
 * @property string $user2_remark 好友备注
 * @property int $active 主动邀请方[1:user1;2:user2]
 * @property int $status 好友状态[0:已解除好友关系;1:好友状态]
 * @property string $agree_time 成为好友时间
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class UsersFriend extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_friends';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user1', 'user2', 'user1_remark', 'user2_remark', 'active', 'status', 'agree_time', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user1' => 'integer', 'user2' => 'integer', 'active' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    /**
     * 获取用户所有好友.
     *
     * @param int $uid 用户ID
     *
     * @return array
     */
    public static function getUserFriends(int $uid) : array
    {
        $sql = <<<SQL
            SELECT users.id,users.nickname,users.avatar,users.motto,users.gender,tmp_table.friend_remark from im_users users
            INNER join
            (
              SELECT id as rid,user2 as uid,user1_remark as friend_remark from im_users_friends where user1 = {$uid} and `status` = 1
                UNION all 
              SELECT id as rid,user1 as uid,user2_remark as friend_remark from im_users_friends where user2 = {$uid} and `status` = 1
            ) tmp_table on tmp_table.uid = users.id  order by tmp_table.rid desc
SQL;
        $rows = Db::select($sql);
        array_walk($rows, static function (&$item) {
            $item = (array) $item;
        });
        return $rows;
    }
    /**
     * 判断用户之间是否存在好友关系.
     *
     * @param int $uid1
     * @param int $uid2
     *
     * @return bool
     */
    public static function isFriend(int $uid1, int $uid2) : bool
    {
        // 比较大小交换位置
        if ($uid1 > $uid2) {
            [$uid1, $uid2] = [$uid2, $uid1];
        }
        return self::where('user1', $uid1)->where('user2', $uid2)->where('status', 1)->exists();
    }
    /**
     * 获取指定用户的所有朋友的用户ID.
     *
     * @param int $user_id 指定用户ID
     * @return array
     */
    public static function getFriendIds(int $user_id) : array
    {
        $sql = "SELECT user2 as uid from im_users_friends where user1 = {$user_id} and `status` = 1 UNION all SELECT user1 as uid from im_users_friends where user2 = {$user_id} and `status` = 1";
        return array_map(static function ($item) {
            return $item->uid;
        }, Db::select($sql));
    }
}