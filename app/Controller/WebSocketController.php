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
namespace App\Controller;

use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\Codec\Json;

/**
 * @example
 * @SocketIONamespace("/")
 */
class WebSocketController extends BaseNamespace
{
    /**
     * @Event("event_keyboard")
     * @param mixed $data
     *
     * @return string
     */
    public function onEventKeyboard(Socket $socket, $data)
    {
        $socket->emit('chat_message', $socket->getSid() . ' say: {}');
        // 应答
        return 'Event Received: string';
    }

    /**
     * @Event("event_talk")
     * @param string $data
     */
    public function onEventTalk(Socket $socket, $data)
    {
        $data = Json::decode($data);
        $socket->to($data['room'])->emit('event', $socket->getSid() . " say: {$data['message']}");
    }
}
