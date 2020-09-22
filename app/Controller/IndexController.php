<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;
use App\Helper\StringHelper;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Coroutine;
use Hyperf\WebSocketClient\ClientFactory;
use Hyperf\WebSocketClient\Frame;
use App\Annotation\Protocol;

/**
 * Class IndexController
 * @package App\Controller
 * @Protocol(name="123")
 */
class IndexController extends AbstractController
{
    /**
     * @Inject
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     *
     * @var
     */
    protected $pro;

    public function index()
    {
//        for ($i = 0;$i<100;$i++){
//            Coroutine::create(function (){
//                // 对端服务的地址，如没有提供 ws:// 或 wss:// 前缀，则默认补充 ws://
//                $host = '127.0.0.1:9502';
//                // 通过 ClientFactory 创建 Client 对象，创建出来的对象为短生命周期对象
//                $client = $this->clientFactory->create($host,false);
//                // 向 WebSocket 服务端发送消息
//                $client->push('HttpServer 中使用 WebSocket Client 发送数据。'.StringHelper::randString(15));
//                // 获取服务端响应的消息，服务端需要通过 push 向本客户端的 fd 投递消息，才能获取；以下设置超时时间 2s，接收到的数据类型为 Frame 对象。
//                /** @var Frame $msg */
//                $msg = $client->recv(2);
//                // 获取文本数据：$res_msg->data
//                $msg->data;
//            });

//        }
    }

}
