<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $mobile 
 * @property string $nickname 
 * @property string $avatar 
 * @property int $gender 
 * @property string $password 
 * @property string $motto 
 * @property string $email 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
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