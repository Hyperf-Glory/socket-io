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
namespace App\JsonRpc;

interface InterfaceCloudService
{
    public function ping(): string;

    public function close(): string;

    public function pushMessage(string $keys, string $message);

    public function broadcast(string $message);

    public function broadcastRoom(string $roomId, string $message);

    public function rooms(): array;
}
