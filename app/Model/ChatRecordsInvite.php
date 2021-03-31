<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $record_id 
 * @property int $type 
 * @property int $operate_user_id 
 * @property string $user_ids 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class ChatRecordsInvite extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_records_invite';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'record_id', 'type', 'operate_user_id', 'user_ids', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'record_id' => 'integer', 'type' => 'integer', 'operate_user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}