<?php

declare (strict_types = 1);
namespace App\Model;

/**
 * @property int            $id
 * @property int            $group_id
 * @property int            $user_id
 * @property int            $leader
 * @property int            $is_mute
 * @property int            $is_quit
 * @property string         $user_card
 * @property string         $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class GroupMember extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'group_member';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'group_id', 'user_id', 'leader', 'is_mute', 'is_quit', 'user_card', 'deleted_at', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'group_id' => 'integer', 'user_id' => 'integer', 'leader' => 'integer', 'is_mute' => 'integer', 'is_quit' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
