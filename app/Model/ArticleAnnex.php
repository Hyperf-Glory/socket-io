<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 文件ID
 * @property int $uid 上传文件的用户ID
 * @property int $article_id 笔记ID
 * @property string $file_suffix 文件后缀名
 * @property int $file_size 文件大小（单位字节）
 * @property string $save_dir 文件保存地址（相对地址）
 * @property string $original_name 原文件名
 * @property int $status 附件状态[1:正常;2:已删除]
 * @property Carbon\Carbon $created_at 附件上传时间
 * @property string $deleted_at 附件删除时间
 */
class ArticleAnnex extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'article_annex';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'uid', 'article_id', 'file_suffix', 'file_size', 'save_dir', 'original_name', 'status', 'created_at', 'deleted_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'uid' => 'integer', 'article_id' => 'integer', 'file_size' => 'integer', 'status' => 'integer', 'created_at' => 'datetime'];
}