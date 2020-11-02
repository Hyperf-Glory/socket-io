<?php
namespace App\Kernel;

use App\Log;
use Firebase\JWT\JWT;
use Hyperf\Redis\Redis;
use Hyperf\SocketIOServer\Parser\Engine;
use Hyperf\SocketIOServer\Parser\Packet;
use Swoole\Coroutine\Channel;
use Swoole\Http\Request;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Swoole\Http\Response;
use Swoole\Timer;
use Swoole\WebSocket\Frame;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use App\Model\User;

class SocketIO extends \Hyperf\SocketIOServer\SocketIO
{
    protected $pingTimeout = 2000;

    protected $pingInterval = 9000; //心跳间隔6秒

    protected $clientCallbackTimeout = 2000;

    /**
     * @Inject()
     * @var Redis
     */
    private $redis;

    /**
     * @param Response|\Swoole\WebSocket\Server $server
     * @param \Swoole\Http\Request              $request
     */
    public function onOpen($server, Request $request): void
    {

        //判断用户是否在其它地方登录

        // 绑定用户与fd该功能

        // 绑定聊天群
        parent::onOpen($server, $request);
    }

    /**
     * @param \Swoole\Http\Response|\Swoole\Server $server
     * @param int                                  $fd
     * @param int                                  $reactorId
     */
    public function onClose($server, int $fd, int $reactorId): void
    {

        // 获取客户端对应的用户ID

        // 清除用户绑定信息

        // 将fd 退出所有聊天室

        // 判断用户是否多平台登录
        parent::onClose($server, $fd, $reactorId);
    }
}
