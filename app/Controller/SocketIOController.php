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
namespace App\Controller;

use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\Codec\Json;


class SocketIOController extends BaseNamespace
{
    /**
     * 聊天对话消息.
     *
     * @param \Hyperf\SocketIOServer\Socket
     * @param  $data
     *
     * @Event("event_talk")
     */
    public function onEventTalk(Socket $socket, $data): string
    {
        $socket->emit('chat_message', $socket->getSid() . ' say: {}');
        // 应答
        return 'Event Received: string';
    }

    /**
     * 键盘输入事件消息.
     *
     * @param \Hyperf\SocketIOServer\Socket
     * @param $data
     * @Event("event_keyboard")
     */
    public function onEventKeyboard(Socket $socket, $data): void
    {
        $data = Json::decode($data);
        $socket->to($data['room'])->emit('event', $socket->getSid() . " say: {$data['message']}");
    }
}
