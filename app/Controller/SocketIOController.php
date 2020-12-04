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

/**
 * Class SocketIOController.
 * @SocketIONamespace("/socket-io")
 */
class SocketIOController extends BaseNamespace
{
    /**
     * 聊天对话消息.
     * @param $data
     * @Event("event_talk")
     */
    public function onEventTalk(Socket $socket, $data)
    {
        dump($data);
    }

    /**
     * 键盘输入事件消息.
     * @param $data
     * @Event("event_keyboard")
     */
    public function onEventKeyboard(Socket $socket, $data)
    {
        dump($data);
    }
}
