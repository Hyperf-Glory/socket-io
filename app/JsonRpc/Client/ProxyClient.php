<?php
declare(strict_types = 1);

namespace App\JsonRpc\Client;

use App\JsonRpc\Contract\InterfaceProxyService;
use Hyperf\RpcClient\AbstractServiceClient;

class ProxyClient extends AbstractServiceClient implements InterfaceProxyService
{

    /**
     * 定义对应服务提供者的服务名称
     * @var string
     */
    protected $serviceName = 'ProxyService';

    /**
     * 定义对应服务提供者的服务协议
     * @var string
     */
    protected $protocol = 'jsonrpc';


    public function publish(string $channel, string $message)
    {

    }

    public function pushMessage(int $uid, string $message)
    {
        // TODO: Implement pushMessage() method.
    }

    public function broadcast(string $message)
    {
        // TODO: Implement broadcast() method.
    }

    public function group(int $groupId, string $message)
    {
        // TODO: Implement group() method.
    }
}
