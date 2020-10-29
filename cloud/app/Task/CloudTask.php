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
use App\Kernel\WebSocket\ClientFactory;
use App\Service\GroupService;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\RedisFactory;
use Hyperf\Server\Server;
use Hyperf\Server\Server as SwooleServer;
use Hyperf\Task\Annotation\Task;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Parallel;
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
     * 根据用户uid查询对应的服务器IP,建立对应服务器的websocket客户端,然后发送消息到对应服务器，服务器自动发送.
     * @Task
     *
     * @param string $uid
     * @param string $message
     */
    public function push(string $uid, string $message)
    {
        $redis = $this->container->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $this->logger->info(sprintf('Cloud push:%s  data:%s', $uid, $message));
        if (!($fd = BindingDependency::fd($redis, $uid))) {
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
        $redis = $this->container->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
        //TODO 1.根据groupid获取uid
        $groupUids = make(GroupService::class)->getGroupUid($groupId);
        $groupUids = array_column($groupUids, 'user_id');
        /**
         * @var array $ips
         */
        $ips         = array_values(config('websocket_server_ips'));
        $parallelCnt = count($ips);
        //利用swoole wait_group
        $parallels   = new Parallel($parallelCnt);
        for ($i = 0; $i < $parallelCnt; $i++) {
            $parallels->add(function () use ($ips, $i,$groupUids)
            {
                $redis = $this->container->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
                //TODO 2.根据ip获取uid
                $ipuids = BindingDependency::getIpUid($redis, $ips[$i]);
                $ipUids = array_intersect($groupUids, $ipuids);
                //TODO 3.取出uid对应的fd
                $fds = BindingDependency::fds($redis, $ipUids);
                //创建 websoket客户端
                $client = $this->container->get(ClientFactory::class)->get('default');
            });
        }

    }

}
