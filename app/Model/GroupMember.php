<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 群成员ID
 * @property int $group_id 群ID
 * @property int $user_id 用户ID
 * @property int $leader 成员属性[0:普通成员;1:管理员;2:群主;]
 * @property int $is_mute 是否禁言[0:否;1:是;]
 * @property int $is_quit 是否退群[0:否;1:是;]
 * @property string $user_card 群名片
 * @property string $deleted_at 退群时间
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class GroupMember extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'group_member';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'group_id', 'user_id', 'leader', 'is_mute', 'is_quit', 'user_card', 'deleted_at', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'group_id' => 'integer', 'user_id' => 'integer', 'leader' => 'integer', 'is_mute' => 'integer', 'is_quit' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}