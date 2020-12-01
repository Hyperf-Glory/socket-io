<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Model\Emoticon;
use App\Services\EmoticonService;
use Psr\Http\Message\ResponseInterface;

class EmoticonController extends AbstractController
{
    /**
     * @var \App\Services\EmoticonService $service
     */
    private $service;

    public function __construct(EmoticonService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    public function getUserEmoticon() : ResponseInterface
    {
        $emoticonList = [];
        $user_id      = $this->uid();

        if ($ids = $this->service->getInstallIds($user_id)) {
            $items = Emoticon::whereIn('id', $ids)->get(['id', 'name', 'url']);
            foreach ($items as $item) {
                $emoticonList[] = [
                    'emoticon_id' => $item->id,
                    'url'         => get_media_url($item->url),
                    'name'        => $item->name,
                    'list'        => $this->emoticonService->getDetailsAll([
                        ['emoticon_id', '=', $item->id],
                        ['user_id', '=', 0]
                    ])
                ];
            }
        }

        return $this->response->success('success', [
            'sys_emoticon'     => $emoticonList,
            'collect_emoticon' => $this->service->getDetailsAll([
                ['emoticon_id', '=', 0],
                ['user_id', '=', $user_id]
            ])
        ]);
    }

    public function getSystemEmoticon() : ResponseInterface
    {

    }

    public function setUserEmoticon() : ResponseInterface
    {

    }

    public function collectEmoticon() : ResponseInterface
    {

    }

    public function uploadEmoticon() : ResponseInterface
    {

    }

    public function delCollectEmoticon() : ResponseInterface
    {

    }
}
