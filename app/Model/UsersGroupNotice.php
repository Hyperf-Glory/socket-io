<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 群公告ID
 * @property int $group_id 群组ID
 * @property int $creator_id 创建者用户ID
 * @property string $title 公告标题
 * @property string $content 公告内容
 * @property int $is_top 是否置顶[0:否;1:是;]
 * @property int $is_delete 是否删除[0:否;1:是;]
 * @property int $is_confirm 是否需群成员确认公告[0:否;1:是;]
 * @property string $confirm_users 已确认成员
 * @property string $deleted_at 删除时间
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
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