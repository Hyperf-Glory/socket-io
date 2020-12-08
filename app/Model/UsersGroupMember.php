<?php

declare(strict_types=1);
/**
 *
 * This file is part of the My App.
 *
 * Copyright CodingHePing 2016-2020.
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/codingheping/hyperf-chat-upgrade
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

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'group_id', 'user_id', 'group_owner', 'status', 'visit_card', 'created_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'group_id' => 'integer', 'user_id' => 'integer', 'group_owner' => 'integer', 'status' => 'integer', 'created_at' => 'datetime'];

    /**
     * 获取聊天群成员ID.
     *
     * @return mixed
     */
    public static function getGroupMemberIds(int $groupId)
    {
        return self::where('group_id', $groupId)->where('status', 0)->pluck('user_id')->toArray();
    }

    /**
     * 获取用户的群名片.
     *
     * @param int $user_id 用户ID
     * @param int $group_id 群ID
     * @return mixed
     */
    public static function visitCard(int $user_id, int $group_id)
    {
        return self::where('group_id', $group_id)->where('user_id', $user_id)->value('visit_card');
    }
}
