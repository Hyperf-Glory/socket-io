<?php

declare(strict_types = 1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Task;

use App\Component\BindingDependency;
use App\Component\ServerSender;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Server\Server;
use Hyperf\Task\Annotation\Task;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

/**
 * Class CloudTask.
 */
class CloudTask
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger    = $container->get(LoggerFactory::class)->get();
    }

    /**
     * @Task
     */
    public function push(string $key, string $message)
    {
        $this->logger->info(sprintf('Cloud push:%s  data:%s', $key, $message));
        if (!($fd = BindingDependency::fd($key))) {
            return;
        }
        if (di(ServerSender::class)->isWsClient($fd)) {
            di(ServerSender::class)->push($message, $fd);
        }
    }

    /**
     * @Task
     */
    public function broadcast(string $message)
    {
        $this->logger->info(sprintf('Cloud push data:%s', $message));
        $fds = di(Server::class)->getServer()->connections;
        Coroutine::create(function () use ($fds, $message)
        {
            foreach ($fds as $fd) {
                di(ServerSender::class)->push($message, $fd);
            }
        });
    }

    /**
     * @Task
     */
    public function broadcastRoom(string $roomId, string $message)
    {
        $this->logger->info(sprintf('Cloud broadcast push roomId:%s  data:%s', $roomId, $message));
        if ((!$fds = BindingDependency::roomfds($roomId))) {
            $this->logger->info(sprintf('roomId :%s is empty not user', $roomId));
            return;
        }
        foreach ($fds as $fd) {
            if (di(ServerSender::class)->isWsClient($fd)) {
                di(ServerSender::class)->push($message, $fd);
                continue;
            }
            $this->logger->info(sprintf("连接fd:{%s} 不是websocket或不存在,待发送数据:%s", $fd, $message));
        }
    }
}
