<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 合并转发ID
 * @property int $record_id 消息记录ID
 * @property int $user_id 转发用户ID
 * @property string $records_id 转发的聊天记录ID，多个用','分割
 * @property string $text 记录快照
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class ChatRecordsForward extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_records_forward';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'record_id', 'user_id', 'records_id', 'text', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'record_id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}