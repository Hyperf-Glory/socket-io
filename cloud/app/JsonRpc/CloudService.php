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
 * @RpcService(name="CloudService", protocol="jsonrpc-tcp-length-check", server="jsonrpc", publishTo="consul")
 */
class CloudService implements InterfaceCloudService
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

    public function ping() : string
    {
        return 'ping';
    }

    public function close() : string
    {
        return 'close';
    }

    /**
     * 接受 logic节点 job节点json-rpc请求，单点推送消息
     * @param string $keys 用户的唯一IDkey
     * @param string $message
     *
     * @return mixed|void
     */
    public function pushMessage(string $keys, string $message)
    {
        $this->logger->debug('cloud node: pushmsg');
        if (empty($keys) || empty($message)) {
            $this->logger->error('cloud json-rpc pushmsg keys message is empty raw data');
            return;
        }
        Coroutine::create(function () use ($keys, $message)
        {
            $keys = explode(',', $keys);
            foreach ($keys as $key) {
                di(CloudTask::class)->push($key, $message);
            }
        });
    }

    /**
     * 广播
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
     * 接受job节点 logic节点 JSON-RPC请求，并进行房间广播
     * @param string $roomId
     * @param string $message
     */
    public function broadcastRoom(string $roomId, string $message)
    {
        if (empty($roomId) || empty($message)) {
            return;
        }
        $this->logger->debug(sprintf('cloud json-rpc broadcastRoom message:%s', $message));
        Coroutine::create(function () use ($roomId, $message)
        {
            di(CloudTask::class)->broadcastRoom($roomId, $message);
        });
    }

    public function rooms() : array
    {
        return [];
    }
}
