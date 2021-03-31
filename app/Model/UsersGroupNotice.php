<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $group_id 
 * @property int $creator_id 
 * @property string $title 
 * @property string $content 
 * @property int $is_top 
 * @property int $is_delete 
 * @property int $is_confirm 
 * @property string $confirm_users 
 * @property string $deleted_at 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
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
    protected $fillable = ['id', 'group_id', 'creator_id', 'title', 'content', 'is_top', 'is_delete', 'is_confirm', 'confirm_users', 'deleted_at', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'group_id' => 'integer', 'creator_id' => 'integer', 'is_top' => 'integer', 'is_delete' => 'integer', 'is_confirm' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}