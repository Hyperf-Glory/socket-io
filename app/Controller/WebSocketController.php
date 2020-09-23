<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Component\ServerSender;
use App\Constants\Log;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Coroutine;
use Hyperf\WebSocketServer\Context;
use Psr\Container\ContainerInterface;
use Swoole\Http\Request;
use Hyperf\Logger\LoggerFactory;
use Swoole\Websocket\Frame;

/**
 * Class WebSocketController
 * @package App\Controller
 */
class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject()
     * @var ServerSender
     */
    protected $sender;

    protected $logger;


    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger    = $container->get(LoggerFactory::class)->get();
    }

    public function onMessage($server, Frame $frame) : void
    {
        $this->logger->info($frame->data);
        $server->push($frame->fd, 'Recv: ' . $frame->data);
        Coroutine::create(function () use ($frame)
        {
            var_dump($this->request);
//            $this->sender->close($frame->fd);
        });
    }

    public function onClose($server, int $fd, int $reactorId) : void
    {
        $this->logger->debug($fd);
        var_dump('closed');
    }

    public function onOpen($server, Request $request) : void
    {
        Context::set(Log::CONTEXT_KEY, $request->fd);
        $server->push($request->fd, 'Opened');
    }
}



