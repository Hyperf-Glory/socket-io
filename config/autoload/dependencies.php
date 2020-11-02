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

use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\Room\RedisNsqAdapter;
use Hyperf\SocketIOServer\SocketIO;
use App\Kernel\SocketIO as KernelSocketIO;

return [
    AdapterInterface::class => RedisNsqAdapter::class,
    SocketIO::class         => KernelSocketIO::class
];
