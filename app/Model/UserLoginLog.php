<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 登录日志ID
 * @property int $user_id 用户ID
 * @property string $ip 登录地址IP
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class UserLoginLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_login_log';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'ip', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}