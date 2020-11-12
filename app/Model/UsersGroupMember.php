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
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property int $group_owner
 * @property int $status
 * @property string $visit_card
 * @property \Carbon\Carbon $created_at
 */
class UsersGroupMember extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_group_member';

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
    protected $casts = ['id' => 'integer', 'group_id' => 'integer', 'user_id' => 'integer', 'group_owner' => 'integer', 'status' => 'integer', 'created_at' => 'datetime'];

    /**
     * 获取聊天群成员ID
     *
     * @param int $groupId
     *
     * @return mixed
     */
    public static function getGroupMemberIds(int $groupId)
    {
        return UsersGroupMember::where('group_id', $groupId)->where('status', 0)->pluck('user_id')->toArray();
    }
}
