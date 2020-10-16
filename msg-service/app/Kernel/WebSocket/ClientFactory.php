<?php
declare(strict_types = 1);

namespace App\Kernel\WebSocket;

use App\Kernel\WebSocket\Exception\InvalidWebSocketProxyException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Exception\InvalidRedisProxyException;

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
     * @param string $poolName
     *
     * @return \Hyperf\WebSocketClient\Client|ClientProxy
     */
    public function get(string $poolName)
    {
        $proxy = $this->proxies[$poolName] ?? NULL;
        if (!$proxy instanceof ClientProxy) {
            throw new InvalidWebSocketProxyException('Invalid WebSocketClient proxy.');
        }

        return $proxy;
    }
}


