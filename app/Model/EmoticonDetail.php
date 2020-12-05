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
 * @property int $emoticon_id 
 * @property int $user_id 
 * @property string $describe 
 * @property string $url 
 * @property string $file_suffix 
 * @property int $file_size 
 * @property \Carbon\Carbon $created_at 
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
    protected $fillable = ['id', 'emoticon_id', 'user_id', 'describe', 'url', 'file_suffix', 'file_size', 'created_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'emoticon_id' => 'integer', 'user_id' => 'integer', 'file_size' => 'integer', 'created_at' => 'datetime'];
}