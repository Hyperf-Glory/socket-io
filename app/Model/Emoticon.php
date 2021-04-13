<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 表情分组ID
 * @property string $name 表情分组名称
 * @property string $url 图片地址
 * @property Carbon\Carbon $created_at 
 * @property Carbon\Carbon $updated_at 
 */
class Emoticon extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'emoticon';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'url', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}