<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
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
