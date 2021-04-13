<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 笔记标签ID
 * @property int $uid 用户ID
 * @property string $tag_name 标签名
 * @property int $sort 排序
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class ArticleTag extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'article_tags';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'uid', 'tag_name', 'sort', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'uid' => 'integer', 'sort' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}