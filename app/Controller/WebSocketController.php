<?php

declare(strict_types = 1);

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
     * @param \Hyperf\SocketIOServer\Socket $socket
     *
     * @return string
     */
    public function onEventKeyboard(\Hyperf\SocketIOServer\Socket $socket, $data)
    {
        $socket->emit('chat_message', $socket->getSid() . " say: {}");
        // 应答
        return 'Event Received: string';
    }


    /**
     * @Event("event_talk")
     * @param \Hyperf\SocketIOServer\Socket $socket
     * @param string                        $data
     */
    public function onEventTalk(Socket $socket, $data)
    {
        $data = Json::decode($data);
        $socket->to($data['room'])->emit('event', $socket->getSid() . " say: {$data['message']}");
    }

}
