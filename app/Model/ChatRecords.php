<?php

declare(strict_types=1);
/**
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/Hyperf-Glory/socket-io
 */
namespace App\Model;

/**
 * @property int $id
 * @property int $source
 * @property int $msg_type
 * @property int $user_id
 * @property int $receive_id
 * @property string $content
 * @property int $is_revoke
 * @property \Carbon\Carbon $created_at
 */
class ChatRecords extends Model
{
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'source', 'msg_type', 'user_id', 'receive_id', 'content', 'is_revoke', 'created_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'source' => 'integer', 'msg_type' => 'integer', 'user_id' => 'integer', 'receive_id' => 'integer', 'is_revoke' => 'integer', 'created_at' => 'datetime'];
}
