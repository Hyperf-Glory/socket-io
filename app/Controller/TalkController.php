<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Helper\ArrayHelper;
use App\Service\TalkService;
use App\Services\Common\UnreadTalk;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

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
     * @return  PsrResponseInterface
     */
    public function list() : PsrResponseInterface
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

    public function create() : PsrResponseInterface
    {

    }

    public function delete() : PsrResponseInterface
    {

    }

    public function topping() : PsrResponseInterface
    {

    }

    public function setNotDisturb() : PsrResponseInterface
    {

    }

    public function updateUnreadNum() : PsrResponseInterface
    {

    }

    public function getChatRecords() : PsrResponseInterface
    {

    }

    public function revokeChatRecords() : PsrResponseInterface
    {

    }

    public function removeChatRecords() : PsrResponseInterface
    {

    }

    public function forwardChatRecords() : PsrResponseInterface
    {

    }

    public function getForwardRecords() : PsrResponseInterface
    {

    }

    public function findChatRecords() : PsrResponseInterface
    {

    }

    public function searchChatRecords() : PsrResponseInterface
    {

    }

    public function getRecordsContext() : PsrResponseInterface
    {

    }

    public function sendImage() : PsrResponseInterface
    {

    }

    public function sendCodeBlock() : PsrResponseInterface
    {

    }

    public function sendFile() : PsrResponseInterface
    {

    }

    public function sendEmoticon() : PsrResponseInterface
    {

    }
}
