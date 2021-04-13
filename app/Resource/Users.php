<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

/**
 * Class Users
 * @package App\Resource
 * @mixin \App\Model\User
 */
class Users extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array
     */
    public function toArray() : array
    {
        return [
            'id'       => $this->id,
            'nickname' => $this->nickname,
            'avatar'   => $this->avatar,
        ];
    }
}
