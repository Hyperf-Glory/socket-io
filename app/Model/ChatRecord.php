<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 聊天记录ID
 * @property int $source 消息来源[1:好友消息;2:群聊消息]
 * @property int $msg_type 消息类型[1:文本消息;2:文件消息;3:系统提示好友入群消息或系统提示好友退群消息;4:会话记录转发]
 * @property int $user_id 发送消息的用户ID[0:代表系统消息]
 * @property int $receive_id 接收消息的用户ID或群聊ID
 * @property string $content 文本消息
 * @property int $is_revoke 是否撤回消息[0:否;1:是]
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
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