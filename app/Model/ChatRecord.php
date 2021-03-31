<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $source 
 * @property int $msg_type 
 * @property int $user_id 
 * @property int $receive_id 
 * @property string $content 
 * @property int $is_revoke 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class ChatRecord extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_records';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'source', 'msg_type', 'user_id', 'receive_id', 'content', 'is_revoke', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'source' => 'integer', 'msg_type' => 'integer', 'user_id' => 'integer', 'receive_id' => 'integer', 'is_revoke' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}