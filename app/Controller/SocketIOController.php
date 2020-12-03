<?php
declare(strict_types = 1);

namespace App\Controller;

use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;

/**
 * Class SocketIOController
 * @package App\Controller
 * @SocketIONamespace("/socket-io")
 */
class SocketIOController extends BaseNamespace
{
    /**
     * 聊天对话消息
     * @param \Hyperf\SocketIOServer\Socket $socket
     * @param                               $data
     * @Event("event_talk")
     */
    public function onEventTalk(Socket $socket, $data)
    {
        dump($data);
    }

    /**
     * 键盘输入事件消息
     * @param \Hyperf\SocketIOServer\Socket $socket
     * @param                               $data
     * @Event("event_keyboard")
     */
    public function onEventKeyboard(Socket $socket, $data){
        dump($data);
    }
}
