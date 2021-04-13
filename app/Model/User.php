<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 用户ID
 * @property string $mobile 手机号
 * @property string $nickname 用户昵称
 * @property string $avatar 用户头像地址
 * @property int $gender 用户性别[0:未知;1:男;2:女]
 * @property string $password 用户密码
 * @property string $motto 用户座右铭
 * @property string $email 用户邮箱
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'mobile', 'nickname', 'avatar', 'gender', 'password', 'motto', 'email', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'gender' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}