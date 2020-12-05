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
namespace App\JsonRpc;

use App\JsonRpc\Contract\InterfaceProxyService;
use App\Task\CloudTask;
use Hyperf\Logger\LoggerFactory;
use Hyperf\RpcServer\Annotation\RpcService;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

/**
 * 该类暂时废弃.
 * @deprecated
 * Class Cloud
 * @RpcService(name="ProxyService", protocol="jsonrpc-tcp-length-check", server="jsonrpc", publishTo="consul")
 */
class ProxyService implements InterfaceProxyService
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
     * @deprecated
     * 单点推送
     * Message:
     *{"event":"event_talk","data":{"send_user":4166,"receive_user":"4167","source_type":"1","text_message":"1"}}
     *
     * @param int $uid 用户的唯一ID
     *
     * @return mixed|void
     */
    public function pushMessage(int $uid, string $message)
    {
        $this->logger->debug('cloud node: pushmsg');
        if (empty($keys) || empty($message)) {
            $this->logger->error('cloud json-rpc pushmsg keys message is empty raw data');
            return;
        }
        di(CloudTask::class)->push((string) $uid, $message);
    }

    /**
     * 广播.
     * @deprecated
     */
    public function broadcast(string $message)
    {
        if (empty($message)) {
            $this->logger->error('cloud json-rpc broadcast  message is empty raw data');
            return;
        }
        $this->logger->debug(sprintf('cloud json-rpc broadcast message:%s', $message));
        Coroutine::create(function () use ($message) {
            di(CloudTask::class)->broadcast($message);
        });
    }

    /**
     * @deprecated
     * 群聊推送
     * Message:
     * {"event":"event_talk","data":{"send_user":4166,"receive_user":"117","source_type":"2","text_message":"2"}}
     *
     * @param int $groupId 群聊ID
     */
    public function group(int $groupId, string $message)
    {
        if (empty($message)) {
            $this->logger->error('cloud json-rpc broadcast  message is empty raw data');
            return;
        }
        $this->logger->debug(sprintf('cloud json-rpc broadcast message:%s', $message));
        Coroutine::create(function () use ($groupId, $message) {
            di(CloudTask::class)->group($groupId, $message);
        });
    }

    /**
     * @deprecated
     */
    public function publish(string $channel, string $message)
    {
        if (empty($message)) {
            $this->logger->error('cloud json-rpc publish  message is empty raw data');
            return;
        }
        if (empty($channel)) {
            $this->logger->error('cloud json-rpc publish  channel is empty raw data');
            return;
        }
    }
}
