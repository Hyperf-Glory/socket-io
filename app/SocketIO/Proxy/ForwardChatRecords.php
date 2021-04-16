<?php
declare(strict_types = 1);

namespace App\SocketIO\Proxy;

use App\Component\MessageParser;
use App\Constants\WsMessage;
use App\Model\ChatRecord;
use App\Model\ChatRecordsForward;
use App\Model\User;
use App\SocketIO\SocketIO as KernelSocketIO;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;

/**
 * 推送聊天记录转发消息.
 * Class ForwardChatRecords
 * @package App\SocketIO
 */
class ForwardChatRecords implements ProxyInterface
{

    public function process($record)
    {
        $rows            = ChatRecordsForward::leftJoin('users', 'users.id', '=', 'chat_records_forward.user_id')
                                             ->leftJoin('chat_records', 'chat_records.id', '=', 'chat_records_forward.record_id')
                                             ->whereIn('chat_records_forward.record_id', $record)
                                             ->get([
                                                 'chat_records.id',
                                                 'chat_records.source',
                                                 'chat_records.msg_type',
                                                 'chat_records.user_id',
                                                 'chat_records.receive_id',
                                                 'chat_records.content',
                                                 'chat_records.is_revoke',
                                                 'chat_records.created_at',
                                                 'users.nickname',
                                                 'users.avatar as avatar',
                                                 'chat_records_forward.records_id',
                                                 'chat_records_forward.text',
                                             ]);
        $socketIoService = make(SocketIOService::class);
        $redis           = ApplicationContext::getContainer()->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
        /**
         * @var ChatRecord|ChatRecordsForward|User $recordRow
         */
        foreach ($rows as $recordRow) {
            if ($recordRow->source === 1) {
                //好友推送
                $client = $redis->hGet(KernelSocketIO::HASH_UID_TO_SID_PREFIX, (string)$recordRow->receive_id);
            } else {
                //群聊推送
                $client = 'room' . $recordRow->receive_id;
            }
            $data = [
                'send_user'    => $recordRow->user_id,
                'receive_user' => $recordRow->receive_id,
                'source_type'  => $recordRow->source,
                'data'         => MessageParser::formatTalkMsg([
                    'id'         => $recordRow->id,
                    'msg_type'   => $recordRow->msg_type,
                    'source'     => $recordRow->source,
                    'avatar'     => $recordRow->avatar,
                    'nickname'   => $recordRow->nickname,
                    'user_id'    => $recordRow->user_id,
                    'receive_id' => $recordRow->receive_id,
                    'created_at' => $recordRow->created_at,
                    'forward'    => [
                        'num'  => substr_count($recordRow->records_id, ',') + 1,
                        'list' => MessageParser::decode($recordRow->text) ?? [],
                    ],
                ]),
            ];
            $socketIoService->push($client, WsMessage::EVENT_REVOKE_RECORDS, $data);
            $socketIoService->push($redis->hGet(KernelSocketIO::HASH_UID_TO_SID_PREFIX, (string)$recordRow->user_id), WsMessage::EVENT_CHAT_MESSAGE, $data);
        }
    }
}
