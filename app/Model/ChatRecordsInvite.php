<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 入群或退群通知ID
 * @property int $record_id 消息记录ID
 * @property int $type 通知类型[1:入群通知;2:自动退群;3:管理员踢群]
 * @property int $operate_user_id 操作人的用户ID(邀请人)
 * @property string $user_ids 用户ID，多个用','分割
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class ChatRecordsInvite extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_records_invite';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'record_id', 'type', 'operate_user_id', 'user_ids', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'record_id' => 'integer', 'type' => 'integer', 'operate_user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}