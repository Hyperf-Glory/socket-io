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
namespace App\Component;

use Hyperf\Utils\Coroutine;
use Hyperf\WebSocketServer\Sender;
use Swoole\Server;

class ServerSender
{
    /**
     * @param $data
     */
    public function sendToAll($data, array $fds = []): void
    {
        foreach ($fds as $fd) {
            $this->push($fd, $data);
        }
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function push($data, int $fd)
    {
        return di(Sender::class)->push($fd, $data);
    }

    /**
     * Disconnect for client, will trigger onClose.
     *
     * @return bool|mixed
     */
    public function disconnect(int $fd, int $code = 0, string $reason = '')
    {
        return di(Sender::class)->disconnect($fd, $code, $reason);
    }

    public function close(int $fd): void
    {
        if (Coroutine::inCoroutine()) {
            Coroutine::create(function () use ($fd) {
                self::disconnect($fd);
            });
        }
        di(Server::class)->close($fd);
    }

    /**
     * @return bool
     */
    public function isWsClient(int $fd)
    {
        $swooleServer = di(\Hyperf\Server\Server::class)->getServer();
        $client = $swooleServer->getClientInfo($fd);
        if (isset($client['websocket_status']) && $client['websocket_status'] === WEBSOCKET_STATUS_FRAME) {
            return true;
        }
        return false;
    }
}
