<?php
declare(strict_types = 1);
namespace App\Controller\Http;

use App\Component\Proxy;
use App\Controller\AbstractController;
use Hyperf\Utils\Coroutine;
use App\Service\GroupService;
use Hyperf\HttpServer\Contract\RequestInterface;

class GroupController extends AbstractController
{

    private GroupService $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function create(RequestInterface $request)
    {
        $params  = $request->all();
        $friends = array_filter(explode(',', $params['uids']));
        [$bool, $data] = $this->groupService->create($this->uid(), [
            'name'    => $params['group_name'],
            'avatar'  => '',
            'profile' => $params['group_profile'],
        ], array_unique($friends));
        if ($bool) {
            //群聊创建成功后需要创建聊天室并发送消息通知
            Coroutine::create(function () use ($data)
            {
                $proxy = $this->container->get(Proxy::class);
                $proxy->groupNotify($data['data']['record_id']);
            });
        }
    }
}

