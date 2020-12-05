<?php

declare (strict_types=1);
/**
 *
 * This file is part of the My App.
 *
 * Copyright CodingHePing 2016-2020.
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/codingheping/hyperf-chat-upgrade
 */
namespace App\Model;

/**
 * @property int $id 
 * @property int $user_id 
 * @property int $article_id 
 * @property string $file_suffix 
 * @property int $file_size 
 * @property string $save_dir 
 * @property string $original_name 
 * @property int $status 
 * @property \Carbon\Carbon $created_at 
 * @property string $deleted_at 
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
    protected $fillable = ['id', 'user_id', 'article_id', 'file_suffix', 'file_size', 'save_dir', 'original_name', 'status', 'created_at', 'deleted_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'article_id' => 'integer', 'file_size' => 'integer', 'status' => 'integer', 'created_at' => 'datetime'];
}