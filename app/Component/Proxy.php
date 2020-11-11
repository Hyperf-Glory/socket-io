<?php
declare(strict_types = 1);

namespace App\Component;

use App\Kernel\SocketIO as KernelSocketIO;
use App\Model\ChatRecords;
use App\Model\ChatRecordsForward;
use App\Model\ChatRecordsInvite;
use App\Model\User;
use Hyperf\Redis\RedisFactory;
use Hyperf\SocketIOServer\SocketIO;

class Proxy
{
    /**
     * 邀请入群通知  踢出群聊通知  自动退出群聊
     *
     * @param int $record
     *
     * @throws \Exception
     */
    public function groupNotify(int $record)
    {
        /**
         * @var ChatRecords $recordInfo
         */
        $recordInfo = ChatRecords::where('id', $record)->where('source', 2)->first([
            'id',
            'msg_type',
            'user_id',
            'receive_id',
            'created_at'
        ]);
        if (!$recordInfo) {
            throw new \Exception('fail');
        }
        /**
         * @var ChatRecordsInvite $notifyInfo
         */
        $notifyInfo = ChatRecordsInvite::where('record_id', $record)->first([
            'record_id',
            'type',
            'operate_user_id',
            'user_ids'
        ]);

        if (!$notifyInfo) {
            throw new \Exception('fail');
        }

        /**
         * @var User $userInfo
         */
        $userInfo = User::where('id', $notifyInfo->operate_user_id)->first(['nickname', 'id']);

        $membersIds = explode(',', $notifyInfo->user_ids);

        $io = di(SocketIO::class);
        //推送群聊消息
        $io->to('room' . (string)$recordInfo->receive_id)->emit('chat_message', [
            'send_user'    => 0,
            'receive_user' => $recordInfo->receive_id,
            'source_type'  => 2,
            'data'         => PushMessageHelper::formatTalkMsg([
                "id"         => $recordInfo->id,
                "source"     => 2,
                "msg_type"   => 3,
                "user_id"    => 0,
                "receive_id" => $recordInfo->receive_id,
                "invite"     => [
                    'type'         => $notifyInfo->type,
                    'operate_user' => ['id' => $userInfo->id, 'nickname' => $userInfo->nickname],
                    'users'        => User::select(['id', 'nickname'])->whereIn('id', $membersIds)->get()->toArray()
                ],
                "created_at" => $recordInfo->created_at,
            ])
        ]);
    }

    /**
     *  推送好友撤销消息事件
     *
     * @param int $record
     *
     * @throws \Exception
     */
    public function revokeRecords(int $record)
    {
        /**
         * @var ChatRecords $records
         */
        $records = ChatRecords::where('id', $record)->first(['id', 'source', 'user_id', 'receive_id']);
        if (!$records) {
            throw new \Exception('数据不存在...');
        }
        $io = di(SocketIO::class);
        //TODO 好友或群聊推送
        if ($records->source == 1) {
            //好友推送
            $redis     = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
            $friendSid = $$redis->hGet(KernelSocketIO::HASH_UID_TO_FD_PREFIX, (string)$records->receive_id);

            $io->to($friendSid)->emit('revoke_records', [
                'record_id'  => $records->id,
                'source'     => $records->source,
                'user_id'    => $records->user_id,
                'receive_id' => $records->receive_id,
            ]);
        } else {
            //群聊推送
            $io->to('room' . (string)$records->receive_id)->emit('revoke_records', [
                'record_id'  => $records->id,
                'source'     => $records->source,
                'user_id'    => $records->user_id,
                'receive_id' => $records->receive_id,
            ]);
        }
    }

    /**
     * 推送聊天记录转发消息
     *
     * @param int $records
     */
    public function forwardChatRecords(int $records)
    {
        $rows = ChatRecordsForward::leftJoin('users', 'users.id', '=', 'chat_records_forward.user_id')
                                  ->leftJoin('chat_records', 'chat_records.id', '=', 'chat_records_forward.record_id')
                                  ->whereIn('chat_records_forward.record_id', $records)
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
        $io   = di(SocketIO::class);
        foreach ($rows as $records) {
            if ($records->source == 1) {
                //好友推送
                $redis     = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
                $friendSid = $$redis->hGet(KernelSocketIO::HASH_UID_TO_FD_PREFIX, (string)$records->receive_id);

                $io->to($friendSid)->emit('revoke_records', [
                    'send_user'    => $records->user_id,
                    'receive_user' => $records->receive_id,
                    'source_type'  => $records->source,
                    'data'         => PushMessageHelper::formatTalkMsg([
                        'id'         => $records->id,
                        'msg_type'   => $records->msg_type,
                        'source'     => $records->source,
                        'avatar'     => $records->avatar,
                        'nickname'   => $records->nickname,
                        "user_id"    => $records->user_id,
                        "receive_id" => $records->receive_id,
                        "created_at" => $records->created_at,
                        "forward"    => [
                            'num'  => substr_count($records->records_id, ',') + 1,
                            'list' => MessageParser::decode($records->text) ?? []
                        ]
                    ])
                ]);
            } else {
                //群聊推送
                $io->to('room' . (string)$records->receive_id)->emit('revoke_records', [
                    'send_user'    => $records->user_id,
                    'receive_user' => $records->receive_id,
                    'source_type'  => $records->source,
                    'data'         => PushMessageHelper::formatTalkMsg([
                        'id'         => $records->id,
                        'msg_type'   => $records->msg_type,
                        'source'     => $records->source,
                        'avatar'     => $records->avatar,
                        'nickname'   => $records->nickname,
                        "user_id"    => $records->user_id,
                        "receive_id" => $records->receive_id,
                        "created_at" => $records->created_at,
                        "forward"    => [
                            'num'  => substr_count($records->records_id, ',') + 1,
                            'list' => MessageParser::decode($records->text) ?? []
                        ]
                    ])
                ]);
            }
        }
    }

    /**
     * 根据消息ID推送客户端
     */
    public function pushTalkMessage()
    {

    }
}
