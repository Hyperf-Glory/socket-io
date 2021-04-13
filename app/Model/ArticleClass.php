<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 笔记分类ID
 * @property int $uid 用户ID
 * @property string $class_name 分类名
 * @property int $sort 排序
 * @property int $is_default 默认分类[1:是;0:不是]
 * @property Carbon\Carbon $created_at 创建时间
 */
class ArticleClass extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'article_class';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'uid', 'class_name', 'sort', 'is_default', 'created_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'uid' => 'integer', 'sort' => 'integer', 'is_default' => 'integer', 'created_at' => 'datetime'];
}