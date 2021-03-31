<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $creator_id 
 * @property string $group_name 
 * @property string $profile 
 * @property string $avatar 
 * @property int $max_num 
 * @property int $is_overt 
 * @property int $is_mute 
 * @property int $is_dismiss 
 * @property string $dismissed_at 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Group extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'group';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'creator_id', 'group_name', 'profile', 'avatar', 'max_num', 'is_overt', 'is_mute', 'is_dismiss', 'dismissed_at', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'creator_id' => 'integer', 'max_num' => 'integer', 'is_overt' => 'integer', 'is_mute' => 'integer', 'is_dismiss' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}