<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 笔记ID
 * @property int $uid 用户ID
 * @property int $class_id 分类ID
 * @property string $tags_id 笔记关联标签
 * @property string $title 笔记标题
 * @property string $abstract 笔记摘要
 * @property string $image 笔记首图
 * @property int $is_asterisk 是否星标笔记[0:否;1:是]
 * @property int $status 笔记状态[1:正常;2:已删除]
 * @property Carbon\Carbon $created_at 添加时间
 * @property Carbon\Carbon $updated_at 最后一次更新时间
 * @property string $deleted_at 笔记删除时间
 */
class Article extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'article';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'uid', 'class_id', 'tags_id', 'title', 'abstract', 'image', 'is_asterisk', 'status', 'created_at', 'updated_at', 'deleted_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'uid' => 'integer', 'class_id' => 'integer', 'is_asterisk' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}