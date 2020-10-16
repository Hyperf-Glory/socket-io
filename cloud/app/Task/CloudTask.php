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
namespace App\Task;

use App\Component\ServerSender;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Server\Server;
use Hyperf\Task\Annotation\Task;
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
        $this->logger = $container->get(LoggerFactory::class)->get();
    }

    /**
     * @Task
     */
    public function push(string $key, string $message)
    {
        $this->logger->info(sprintf('Cloud push:%s  data:%s', $key, $message));
    }

    /**
     * @Task
     */
    public function broadcast(string $message)
    {
        $this->logger->info(sprintf('Cloud push data:%s', $message));
        $fds = di(Server::class)->getServer()->connections;
        foreach ($fds as $fd) {
            di(ServerSender::class)->push($message, $fd);
        }
    }

    /**
     * @Task
     */
    public function broadcastRoom(string $roomId, string $message)
    {
        $this->logger->info(sprintf('Cloud broadcast push roomId:%s  data:%s', $roomId, $message));
        //TODO 获取对应房间内的fd
    }
}
