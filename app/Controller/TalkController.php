<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Services\Common\UnreadTalk;

class TalkController extends AbstractController
{
    /**
     * //TODO 11.13日需要完成的
     */
    public function list()
    {
        $user   = $this->request->getAttribute('user');
        $result = di(UnreadTalk::class)->getAll($user['id']);
        if($result){

        }
    }
}
