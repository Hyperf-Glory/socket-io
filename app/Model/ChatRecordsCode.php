<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 入群或退群通知ID
 * @property int $record_id 消息记录ID
 * @property int $user_id 上传文件的用户ID
 * @property string $code_lang 代码片段类型(如：php,java,python)
 * @property string $code 代码片段内容
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class ChatRecordsCode extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_records_code';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'record_id', 'user_id', 'code_lang', 'code', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'record_id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}