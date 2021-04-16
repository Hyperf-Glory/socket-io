<?php
declare(strict_types = 1);

namespace App\SocketIO\Proxy;

use App\Component\MessageParser;
use App\Constants\WsMessage;
use App\Model\ChatRecord;
use App\Model\ChatRecordsInvite;
use App\Model\User;
use App\SocketIO\SocketIOService;
use RuntimeException;

/**
 * 群聊通知
 * Class GroupNotify
 * @package App\SocketIO\Proxy
 */
class GroupNotify implements ProxyInterface
{

    public function process($record)
    {
        $socketIoService = make(SocketIOService::class);
        /**
         * @var \App\Model\ChatRecord $recordInfo
         */
        $recordInfo = ChatRecord::where('id', $record)->where('source', 2)->first([
            'id',
            'msg_type',
            'user_id',
            'receive_id',
            'created_at',
        ]);

        if (!$recordInfo) {
            throw new RuntimeException('fail');
        }
        /**
         * @var ChatRecordsInvite $notifyInfo
         */
        $notifyInfo = ChatRecordsInvite::where('record_id', $record)->first([
            'record_id',
            'type',
            'operate_user_id',
            'user_ids',
        ]);

        if (!$notifyInfo) {
            throw new RuntimeException('fail');
        }

        /**
         * @var User $userInfo
         */
        $userInfo = User::where('id', $notifyInfo->operate_user_id)->first(['nickname', 'id']);

        $membersIds = explode(',', $notifyInfo->user_ids);

        $socketIoService->push('room' . $recordInfo->receive_id, WsMessage::EVENT_CHAT_MESSAGE, [
            'send_user'    => 0,
            'receive_user' => $recordInfo->receive_id,
            'source_type'  => 2,
            'data'         => MessageParser::formatTalkMsg([
                'id'         => $recordInfo->id,
                'source'     => 2,
                'msg_type'   => 3,
                'user_id'    => 0,
                'receive_id' => $recordInfo->receive_id,
                'invite'     => [
                    'type'         => $notifyInfo->type,
                    'operate_user' => ['id' => $userInfo->id, 'nickname' => $userInfo->nickname],
                    'users'        => User::select(['id', 'nickname'])->whereIn('id', $membersIds)->get()->toArray(),
                ],
                'created_at' => $recordInfo->created_at,
            ]),
        ]);
    }

}
