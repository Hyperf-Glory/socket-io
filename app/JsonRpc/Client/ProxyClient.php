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
namespace App\JsonRpc\Client;

use App\JsonRpc\Contract\InterfaceProxyService;
use Hyperf\RpcClient\AbstractServiceClient;

/**
 * @deprecated
 * Class ProxyClient
 */
class ProxyClient extends AbstractServiceClient implements InterfaceProxyService
{
    /**
     * 定义对应服务提供者的服务名称.
     * @var string
     */
    protected $serviceName = 'ProxyService';

    /**
     * 定义对应服务提供者的服务协议.
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
