<?php

declare(strict_types=1);
/**
 *
 * This file is part of the My App.
 *
 * Copyright CodingHePing 2016-2020.
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/codingheping/hyperf-chat-upgrade
 */
namespace App\Task;

use App\Component\ClientManager;
use App\Kernel\WebSocket\ClientFactory;
use App\Service\GroupService;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\RedisFactory;
use Hyperf\Task\Annotation\Task;
use Hyperf\Utils\Exception\ParallelExecutionException;
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
        $this->logger = $container->get(LoggerFactory::class)->get();
    }

    /**
     * 单点推送
     * 根据用户uid查询对应的服务器IP,建立对应服务器的websocket客户端,然后发送消息到对应服务器，服务器自动发送.
     * @Task
     *{"event":"event_talk","data":{"send_user":4166,"receive_user":"4167","source_type":"1","text_message":"1"},"type":"push"}
     */
    public function push(string $uid, string $message)
    {
        $redis = $this->container->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $this->logger->info(sprintf('Cloud push:%s  data:%s', $uid, $message));
        if (! ($fd = ClientManager::fd($redis, $uid))) {
            return;
        }
    }

    /**
     * 广播
     * 获取所有的websocket服务器IP,然后进行推送
     * @Task
     *
     * @return array
     */
    public function broadcast(string $message)
    {
        $this->logger->info(sprintf('Cloud push data:%s', $message));
        /**
         * @var array $ips
         */
        $serverIps = (config('websocket_server_ips'));
        $ips = array_values($serverIps);
        $parallelCnt = count($ips);
        $parallel = new Parallel($parallelCnt);
        foreach ($serverIps as $server => $ip) {
            $parallel->add(function () use ($ip, $server, $message) {
                $client = $this->container->get(ClientFactory::class)->get($server);
                return $client->push($message);
            });
        }
        try {
            return $parallel->wait();
        } catch (ParallelExecutionException $e) {
            /**
             * @var ParallelExecutionException $ex
             */
            foreach ($e->getThrowables() as $server => $ex) {
                $this->logger->error(sprintf('Server[%s]广播推送消息发生错误:%s[%s] in %s', $server, $ex->getMessage(), $ex->getLine(), $ex->getFile()));
            }
            foreach ($e->getResults() as $server => $result) {
                $this->logger->info(sprintf('广播推送消息[%s]结果为[%s]', $server, $result === true ? '成功' : '失败'));
            }
            return $e->getResults();
        }
    }

    /**
     * 群聊
     * 根据群聊group_id,获取所有的uid,根据uid获取对应的服务器ip，然后进行推送
     *{"event":"event_talk","data":{"send_user":4166,"receive_user":"117","source_type":"2","text_message":"2"},"type":"group"}.
     *
     * @return null|array
     */
    public function group(int $groupId, string $message)
    {
        $this->logger->info(sprintf('Cloud push group:%s  data:%s', $groupId, $message));
        if (empty($groupId)) {
            return null;
        }

        $groupUids = make(GroupService::class)->getGroupUid($groupId);
        $groupUids = array_column($groupUids, 'user_id');
        /**
         * @var array $ips
         */
        $serverIps = (config('websocket_server_ips'));

        $ips = array_values($serverIps);

        $parallelCnt = count($ips);

        $parallel = new Parallel($parallelCnt);

        foreach ($serverIps as $server => $ip) {
            $parallel->add(function () use ($ip, $server, $groupUids, $message, $groupId) {
                $redis = $this->container->get(RedisFactory::class)->get(env('CLOUD_REDIS'));

                $ipuids = ClientManager::getIpUid($redis, $ip);

                $uids = array_intersect($groupUids, $ipuids);

                $fds = ClientManager::fds($redis, $uids);

                if (empty($fds)) {
                    throw new ParallelExecutionException(sprintf('Cloud push group:%s  server:%s data:%s,当前服务器暂无群员的在线用户', $groupId, $server, $message));
                }

                $client = $this->container->get(ClientFactory::class)->get($server);

                return $client->push($message);
            });
        }
        try {
            return $parallel->wait();
        } catch (ParallelExecutionException $e) {
            /**
             * @var ParallelExecutionException $ex
             */
            foreach ($e->getThrowables() as $server => $ex) {
                $this->logger->error(sprintf('群组推送消息发生错误:%s[%s] in %s', $ex->getMessage(), $ex->getLine(), $ex->getFile()));
            }
            foreach ($e->getResults() as $server => $result) {
                $this->logger->info(sprintf('群组推送消息[%s]结果为[%s]', $server, $result === true ? '成功' : '失败'));
            }
            return $e->getResults();
        }
    }

    public function publish(string $channel, string $message)
    {
    }
}
