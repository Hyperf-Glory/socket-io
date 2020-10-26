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
use Hyperf\Server\Server as SwooleServer;
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
     *
     * @param string $key
     * @param string $message
     */
    public function push(string $key, string $message)
    {
        $this->logger->info(sprintf('Cloud push:%s  data:%s', $key, $message));
        if (!($fd = BindingDependency::fd($key))) {
            return;
        }
        di(SwooleServer::class)->getServer()->send($fd, $message);
    }

    /**
     * @Task
     *
     * @param string $message
     */
    public function broadcast(string $message)
    {
        $this->logger->info(sprintf('Cloud push data:%s', $message));
        $fds = di(Server::class)->getServer()->connections;
        Coroutine::create(function () use ($fds, $message)
        {
            foreach ($fds as $fd) {
                di(SwooleServer::class)->getServer()->send($fd, $message);
            }
        });
    }

}
