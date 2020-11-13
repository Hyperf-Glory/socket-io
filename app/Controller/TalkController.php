<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Helper\ArrayHelper;
use App\Service\TalkService;
use App\Services\Common\UnreadTalk;

class TalkController extends AbstractController
{
    private $service;

    public function __construct(TalkService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    /**
     * 获取用户对话列表
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function list()
    {
        $user   = $this->request->getAttribute('user');
        $result = di(UnreadTalk::class)->getAll($user['id']);
        if ($result) {
            $this->service->updateUnreadTalkList($user['id'], $result);
        }
        // 获取聊天列表
        $rows = $this->service->talks($user['id']);
        if ($rows) {
            $rows = ArrayHelper::sortByField($rows, 'updated_at');
        }
        return $this->response->success('success', $rows);
    }
}
