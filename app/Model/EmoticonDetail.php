<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 表情包ID
 * @property int $emoticon_id 表情分组ID
 * @property int $user_id 用户ID（0：代码系统表情包）
 * @property string $describe 表情关键字描述
 * @property string $url 表情链接
 * @property string $file_suffix 文件后缀名
 * @property int $file_size 文件大小（单位字节）
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class EmoticonDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'emoticon_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'emoticon_id', 'user_id', 'describe', 'url', 'file_suffix', 'file_size', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'emoticon_id' => 'integer', 'user_id' => 'integer', 'file_size' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}