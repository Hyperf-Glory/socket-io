<?php

declare(strict_types=1);
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
 * @property int            $user_id
 * @property string         $group_name
 * @property string         $group_profile
 * @property int            $status
 * @property string         $avatar
 * @property \Carbon\Carbon $created_at
 */
class UsersGroup extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_group';

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
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'status' => 'integer', 'created_at' => 'datetime'];

    /**
     * 判断用户是否是管理员.
     *
     * @param int $uid 用户ID
     * @param int $groupId 群ID
     *
     * @return mixed
     */
    public static function isManager(int $uid, int $groupId)
    {
        return UsersGroup::where('id', $groupId)->where('user_id', $uid)->exists();
    }

    /**
     * 判断用户是否是群成员.
     *
     * @param int $groupId 群ID
     * @param int $uid 用户ID
     *
     * @return bool
     */
    public static function isMember(int $groupId, int $uid)
    {
        return UsersGroupMember::where('group_id', $groupId)->where('user_id', $uid)->where('status', 0)->exists();
    }
}
