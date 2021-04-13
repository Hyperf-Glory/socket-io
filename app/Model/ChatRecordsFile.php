<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 文件ID
 * @property int $record_id 消息记录ID
 * @property int $user_id 上传文件的用户ID
 * @property int $file_source 文件来源[1:用户上传;2:表情包]
 * @property int $file_type 消息类型[1:图片;2:视频;3:文件]
 * @property int $save_type 文件保存方式（0:本地 1:第三方[阿里OOS、七牛云] ）
 * @property string $original_name 原文件名
 * @property string $file_suffix 文件后缀名
 * @property int $file_size 文件大小（单位字节）
 * @property string $save_dir 文件保存地址（相对地址/第三方网络地址）
 * @property int $is_delete 文件是否已删除[0:否;1:已删除]
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class ChatRecordsFile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_records_file';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'record_id', 'user_id', 'file_source', 'file_type', 'save_type', 'original_name', 'file_suffix', 'file_size', 'save_dir', 'is_delete', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'record_id' => 'integer', 'user_id' => 'integer', 'file_source' => 'integer', 'file_type' => 'integer', 'save_type' => 'integer', 'file_size' => 'integer', 'is_delete' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}