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
namespace App\JsonRpc;

use App\Task\CloudTask;
use Hyperf\Logger\LoggerFactory;
use Hyperf\RpcServer\Annotation\RpcService;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

/**
 * Class Cloud.
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
        $this->logger    = $container->get(LoggerFactory::class)->get();
    }

    /**
     * 单点推送
     * Message:
     *{"event":"event_talk","data":{"send_user":4166,"receive_user":"4167","source_type":"1","text_message":"1"}}
     * @param int    $uid 用户的唯一ID
     * @param string $message
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
        di(CloudTask::class)->push((string)$uid, $message);
    }

    /**
     * 广播
     *
     * @param string $message
     */
    public function broadcast(string $message)
    {
        if (empty($message)) {
            $this->logger->error('cloud json-rpc broadcast  message is empty raw data');
            return;
        }
        $this->logger->debug(sprintf('cloud json-rpc broadcast message:%s', $message));
        Coroutine::create(function () use ($message)
        {
            di(CloudTask::class)->broadcast($message);
        });
    }

    /**
     * 群聊推送
     * Message:
     * {"event":"event_talk","data":{"send_user":4166,"receive_user":"117","source_type":"2","text_message":"2"}}
     * @param int    $groupId 群聊ID
     * @param string $message
     */
    public function group(int $groupId, string $message)
    {
        if (empty($message)) {
            $this->logger->error('cloud json-rpc broadcast  message is empty raw data');
            return;
        }
        $this->logger->debug(sprintf('cloud json-rpc broadcast message:%s', $message));
        Coroutine::create(function () use ($groupId, $message)
        {
            di(CloudTask::class)->group($groupId, $message);
        });
    }
}
