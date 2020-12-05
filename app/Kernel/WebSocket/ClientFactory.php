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
namespace App\Kernel\WebSocket;

use App\Kernel\WebSocket\Exception\InvalidWebSocketProxyException;
use Hyperf\Contract\ConfigInterface;

class ClientFactory
{
    /**
     * @var ClientProxy[]
     */
    protected $proxies;

    public function __construct(ConfigInterface $config)
    {
        $clientConfig = $config->get('websocket_client');

        foreach ($clientConfig as $poolName => $item) {
            $this->proxies[$poolName] = make(ClientProxy::class, ['pool' => $poolName]);
        }
    }

    /**
     * @return ClientProxy|\Hyperf\WebSocketClient\Client
     */
    public function get(string $poolName)
    {
        $proxy = $this->proxies[$poolName] ?? null;
        if (! $proxy instanceof ClientProxy) {
            throw new InvalidWebSocketProxyException('Invalid WebSocketClient proxy.');
        }

        return $proxy;
    }
}
