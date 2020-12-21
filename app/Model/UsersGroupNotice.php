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
 * @property int $group_id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property int $is_delete
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 */
class UsersGroupNotice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_group_notice';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'group_id', 'user_id', 'title', 'content', 'is_delete', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'group_id' => 'integer', 'user_id' => 'integer', 'is_delete' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
