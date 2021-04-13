<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 表情包收藏ID
 * @property int $user_id 用户ID
 * @property string $emoticon_ids 表情包ID
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class UsersEmoticon extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_emoticon';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'emoticon_ids', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}