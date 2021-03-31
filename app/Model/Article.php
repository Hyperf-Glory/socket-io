<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $uid 
 * @property int $class_id 
 * @property string $tags_id 
 * @property string $title 
 * @property string $abstract 
 * @property string $image 
 * @property int $is_asterisk 
 * @property int $status 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property string $deleted_at 
 */
class Article extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'article';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'uid', 'class_id', 'tags_id', 'title', 'abstract', 'image', 'is_asterisk', 'status', 'created_at', 'updated_at', 'deleted_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'uid' => 'integer', 'class_id' => 'integer', 'is_asterisk' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}