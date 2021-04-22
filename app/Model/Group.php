<?php

declare (strict_types = 1);
namespace App\Model;

use Hyperf\Database\Model\Relations\HasMany;

/**
 * @property int           $id           群ID
 * @property int           $creator_id   创建者ID(群主ID)
 * @property string        $group_name   群名称
 * @property string        $profile      群介绍
 * @property string        $avatar       群头像
 * @property int           $max_num      最大群成员数量
 * @property int           $is_overt     是否公开可见[0:否;1:是;]
 * @property int           $is_mute      是否全员禁言 [0:否;1:是;]，提示:不包含群主或管理员
 * @property int           $is_dismiss   是否已解散[0:否;1:是;]
 * @property string        $dismissed_at 解散时间
 * @property Carbon\Carbon $created_at
 * @property Carbon\Carbon $updated_at
 */
class Group extends Model
{
    public const MAX_MEMBER_NUM = 500;
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
     * 获取群聊成员
     */
    public function members() : HasMany
    {
        return $this->hasMany(GroupMember::class, 'group_id', 'id');
    }

    /**
     * 判断用户是否是管理员
     *
     * @param int       $user_id  用户ID
     * @param int       $group_id 群ID
     * @param int|array $leader   管理员类型[0:普通成员;1:管理员;2:群主;]
     *
     * @return bool
     */
    public static function isManager(int $user_id, int $group_id, $leader = 2) : bool
    {
        return self::where('id', $group_id)->where('creator_id', $user_id)->exists();
    }

    /**
     * 判断群组是否已解散
     *
     * @param int $group_id 群ID
     *
     * @return bool
     */
    public static function isDismiss(int $group_id) : bool
    {
        return self::where('id', $group_id)->where('is_dismiss', 1)->exists();
    }

    /**
     * 判断用户是否是群成员
     *
     * @param int $group_id 群ID
     * @param int $user_id  用户ID
     *
     * @return bool
     */
    public static function isMember(int $group_id, int $user_id) : bool
    {
        return GroupMember::where('group_id', $group_id)->where('user_id', $user_id)->where('is_quit', 0)->exists();
    }
}
