<?php

declare(strict_types=1);
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
 * @property int $file_type
 * @property int $user_id
 * @property string $hash_name
 * @property string $original_name
 * @property int $split_index
 * @property int $split_num
 * @property string $save_dir
 * @property string $file_ext
 * @property int $file_size
 * @property int $is_delete
 * @property int $upload_at
 */
class FileSplitUpload extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_split_upload';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'file_type', 'user_id', 'hash_name', 'original_name', 'split_index', 'split_num', 'save_dir', 'file_ext', 'file_size', 'is_delete', 'upload_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'file_type' => 'integer', 'user_id' => 'integer', 'split_index' => 'integer', 'split_num' => 'integer', 'file_size' => 'integer', 'is_delete' => 'integer', 'upload_at' => 'integer'];
}
