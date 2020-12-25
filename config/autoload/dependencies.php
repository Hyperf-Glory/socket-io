<?php

declare(strict_types=1);
/**
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/Hyperf-Glory/socket-io
 */
use App\Kernel\SocketIO\SocketIO as KernelSocketIO;
use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\JsonRpcTransporter;
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\Room\RedisNsqAdapter;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\Utils\Serializer\Serializer;
use Hyperf\Utils\Serializer\SerializerFactory;

return [
    AdapterInterface::class => RedisNsqAdapter::class,
    SocketIO::class => KernelSocketIO::class,
    JsonRpcTransporter::class => JsonRpcPoolTransporter::class,
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
