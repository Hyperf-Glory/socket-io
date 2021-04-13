<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 聊天删除记录ID
 * @property int $record_id 聊天记录ID
 * @property int $user_id 用户ID
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class ChatRecordsDelete extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_records_delete';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'record_id', 'user_id', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'record_id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}