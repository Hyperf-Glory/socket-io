<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 笔记详情ID
 * @property int $article_id 笔记ID
 * @property string $md_content Markdown 内容
 * @property string $content Markdown 解析HTML内容
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class ArticleDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'article_detail';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'article_id', 'md_content', 'content', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'article_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}