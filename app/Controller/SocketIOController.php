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

use App\Cache\LastMsgCache;
use App\Component\MessageParser;
use App\Kernel\SocketIO;
use App\Model\ChatRecords;
use App\Model\UsersFriends;
use App\Services\Common\UnreadTalk;
use Hyperf\Redis\RedisFactory;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;

class SocketIOController extends BaseNamespace
{
    /**
     * 聊天对话消息.
     *
     * @param \Hyperf\SocketIOServer\Socket
     * @param  $data
     * @example {"event":"event_talk","data":{"send_user":4166,"receive_user":"4168","source_type":"1","text_message":"1"}}
     * @Event("event_talk")
     * @todo 待解决消息不能发送的问题
     */
    public function onEventTalk(Socket $socket, $data): bool
    {
        $data = [
            'send_user' => (int) $data['send_user'],
            'receive_user' => (int) $data['receive_user'],
            'source_type' => (int) $data['source_type'],
            'text_message' => $data['text_message'],
        ];

        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        if ($redis->hGet(SocketIO::HASH_UID_TO_SID_PREFIX, (string) ($data['receive_user'] ?? 0)) === $socket->getSid()) {
            $socket->emit('notify', [
                'notify' => '非法操作!!!',
            ]);
            return true;
        }
        //验证消息类型 私聊|群聊
        if (! in_array($data['source_type'], [1, 2], true)) {
            return true;
        }
        //验证发送消息用户与接受消息用户之间是否存在好友或群聊关系
        if ($data['source_type'] === 1) {//私信
            //判断发送者和接受者是否是好友关系
            if (! UsersFriends::isFriend($data['send_user'], $data['receive_user'])) {
                $socket->emit('notify', [
                    'notify' => '温馨提示:您当前与对方尚未成功好友！',
                ]);
                return true;
            }
        } elseif ((int) $data['source_type'] === 2) {//群聊
            //判断是否属于群成员
            if (! UserGroup::isMember($data['receive_user'], $data['send_user'])) {
                $socket->emit('notify', [
                    'notify' => '温馨提示:您还没有加入该聊天群！',
                ]);
                return true;
            }
        }

        $result = ChatRecords::create([
            'source' => $data['source_type'],
            'msg_type' => 1,
            'user_id' => $data['send_user'],
            'receive_id' => $data['receive_user'],
            'content' => htmlspecialchars($data['text_message']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (! $result) {
            return false;
        }
        if ($result->content) {
            $result->content = replace_url_link($result->content);
        }

        $msg = ([
            'send_user' => $data['send_user'],
            'receive_user' => $data['receive_user'],
            'source_type' => $data['source_type'],
            'data' => MessageParser::formatTalkMsg([
                'id' => $result->id,
                'source' => $result->source,
                'msg_type' => 1,
                'user_id' => $result->user_id,
                'receive_id' => $result->receive_id,
                'content' => $result->content,
                'created_at' => $result->created_at->toDateTimeString(),
            ]), ]);
        if ($data['source_type'] === 1) {//私聊
            $msg_text = mb_substr($result->content, 0, 30);
            // 缓存最后一条消息
            LastMsgCache::set([
                'text' => $msg_text,
                'created_at' => $result->created_at,
            ], $data['receive_user'], $data['send_user']);

            // 设置好友消息未读数
            di(UnreadTalk::class)->setInc($result->receive_id, $result->user_id, $redis);

            $socket->to($redis->hGet(SocketIO::HASH_UID_TO_SID_PREFIX, (string) $data['receive_user']))->emit('chat_message', $msg);
            //给自己发送消息
            $socket->emit('chat_message', $msg);

            return true;
        }
        if ($data['source_type'] === 2) {
            $socket->to('room' . $data['receive_user'])->emit('chat_message', $msg);
            return true;
        }
        return false;
    }

    /**
     * 键盘输入事件消息.
     *
     * @param \Hyperf\SocketIOServer\Socket
     * @param $data
     * @Event("event_keyboard")
     * @example {"event":"event_keyboard","data":{"send_user":4166,"receive_user":"4168"}}
     */
    public function onEventKeyboard(Socket $socket, $data): void
    {
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $socket->to($redis->hGet(SocketIO::HASH_UID_TO_SID_PREFIX, (string) $data['receive_user']))->emit('input_tip', $data);
    }
}
