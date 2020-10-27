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
     * 单点推送
     * 根据用户key查询对应的服务器IP,建立对应服务器的websocket客户端,然后发送消息到对应服务器，服务器自动发送.
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
    }

    /**
     * 广播
     * 获取所有的websocket服务器IP,然后进行推送
     * @Task
     *
     * @param string $message
     */
    public function broadcast(string $message)
    {
        $this->logger->info(sprintf('Cloud push data:%s', $message));
    }

    /**
     * 群聊
     * 根据群聊group_id,获取所有的uid,根据uid获取对应的服务器ip，然后进行推送
     *
     * @param int    $groupId
     * @param string $message
     */
    public function group(int $groupId, string $message)
    {
        $this->logger->info(sprintf('Cloud push group:%s  data:%s', $groupId, $message));
        if (empty($groupId)) {
            return;
        }
        //TODO 1.根据groupid获取uid
        $guids = [
          1,2,5,6
        ];
        $ips = [
          '127.0.0.1',
          '127.0.0.2',
          '127.0.0.3',
        ];
        //TODO 2.根据ip获取uid
        $ipuids = BindingDependency::getIpUid(array_rand($ips));
        $ipUids = array_merge($guids,$ipuids);
        //TODO 3.取出uid对应的fd
    }

}
