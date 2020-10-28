<?php
declare(strict_types = 1);

namespace App\Service;

use App\Model\UsersGroupMember;

class GroupService
{
    public function getGroupUid(int $groupId)
    {
        return UsersGroupMember::query()->where('group_id', $groupId)->get('user_id')->toArray();
    }
}
