<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 申请ID
 * @property int $user_id 申请人ID
 * @property int $friend_id 被申请人
 * @property int $status 申请状态[0:等待处理;1:已同意]
 * @property string $remarks 申请人备注信息
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class UsersFriendsApply extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_friends_apply';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'friend_id', 'status', 'remarks', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'friend_id' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}