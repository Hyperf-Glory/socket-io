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
use App\Kernel\SocketIO as KernelSocketIO;
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
