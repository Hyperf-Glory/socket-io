<?php
declare(strict_types = 1);

namespace App\Component;

use App\Model\ChatRecords;
use App\Model\ChatRecordsInvite;
use App\Model\User;
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
     */
    public function revokeRecords(int $record)
    {
        $records = ChatRecords::where('id', $record)->first(['id', 'source', 'user_id', 'receive_id']);
        if (!$records) {
            throw new \Exception('数据不存在...');
        }
        //TODO 好友或群聊推送

    }

    /**
     * 推送聊天记录转发消息
     */
    public function forwardChatRecords()
    {

    }
}
