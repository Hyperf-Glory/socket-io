<?php

declare(strict_types=1);

namespace App\Kernel;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\SocketIOServer\Parser\Decoder;
use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\WebSocketServer\Sender;
use Psr\Container\ContainerInterface;

class SocketIOFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $io = new SocketIO(
            $container->get(StdoutLoggerInterface::class),
            $container->get(Sender::class),
            $container->get(Decoder::class),
            $container->get(Encoder::class),
            $container->get(SidProviderInterface::class)
        );

        // 重写参数，参考https://hyperf.wiki/2.0/#/zh-cn/socketio-server?id=%e4%bf%ae%e6%94%b9-socketio-%e5%9f%ba%e7%a1%80%e5%8f%82%e6%95%b0
        $io->setPingTimeout(10000);
        $io->setPingInterval(10000);
        $io->setClientCallbackTimeout(10000);

        return $io;
    }
}
