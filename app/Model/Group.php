<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 群ID
 * @property int $creator_id 创建者ID(群主ID)
 * @property string $group_name 群名称
 * @property string $profile 群介绍
 * @property string $avatar 群头像
 * @property int $max_num 最大群成员数量
 * @property int $is_overt 是否公开可见[0:否;1:是;]
 * @property int $is_mute 是否全员禁言 [0:否;1:是;]，提示:不包含群主或管理员
 * @property int $is_dismiss 是否已解散[0:否;1:是;]
 * @property string $dismissed_at 解散时间
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class Group extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'group';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'creator_id', 'group_name', 'profile', 'avatar', 'max_num', 'is_overt', 'is_mute', 'is_dismiss', 'dismissed_at', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'creator_id' => 'integer', 'max_num' => 'integer', 'is_overt' => 'integer', 'is_mute' => 'integer', 'is_dismiss' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    /**
     * 判断用户是否是管理员.
     *
     * @param int $uid     用户ID
     * @param int $groupId 群ID
     *
     * @return bool
     */
    public static function isManager(int $uid, int $groupId) : bool
    {
        return self::where('id', $groupId)->where('user_id', $uid)->exists();
    }
    /**
     * 判断用户是否是群成员.
     *
     * @param int $groupId 群ID
     * @param int $uid     用户ID
     *
     * @return bool
     */
    public static function isMember(int $groupId, int $uid) : bool
    {
        return GroupMember::where('group_id', $groupId)->where('user_id', $uid)->where('leader', 0)->exists();
    }
}