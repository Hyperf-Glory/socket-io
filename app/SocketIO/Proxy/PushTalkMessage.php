<?php
declare(strict_types = 1);

namespace App\SocketIO\Proxy;

use App\Component\MessageParser;
use App\Constants\WsMessage;
use App\Model\ChatRecordsCode;
use App\Model\ChatRecordsFile;
use App\SocketIO\SocketIO as KernelSocketIO;
use App\SocketIO\SocketIOService;
use Hyperf\Redis\RedisFactory;
use App\Model\User;
use App\Model\ChatRecord;
use RuntimeException;
use Hyperf\Utils\ApplicationContext;

/**
 * 根据消息ID推送客户端.
 * Class PushTalkMessage
 * @package App\SocketIO\Proxy
 */
class PushTalkMessage implements ProxyInterface
{
    public function process($record)
    {
        /**
         * @var \App\Model\ChatRecord|User $info
         */
        $info = ChatRecord::leftJoin('users', 'users.id', '=', 'chat_records.user_id')->where('chat_records.id', $record)->first([
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
        ]);
        if (!$info) {
            throw new RuntimeException('fail');
        }
        $socketIoService = make(SocketIOService::class);
        $file            = [];
        $codeBlock       = [];
        if ($info->msg_type === 2) {
            $file = ChatRecordsFile::where('record_id', $info->id)->first(['id', 'record_id', 'user_id', 'file_source', 'file_type', 'save_type', 'original_name', 'file_suffix', 'file_size', 'save_dir']);
            $file = $file ? $file->toArray() : [];
            if ($file) {
                $file['file_url'] = config('image_url') . '/' . $file['save_dir'];
            }
        } elseif ($info->msg_type === 5) {
            $codeBlock = ChatRecordsCode::where('record_id', $info->id)->first(['record_id', 'code_lang', 'code']);
            $codeBlock = $codeBlock ? $codeBlock->toArray() : [];
        }

        $data = [
            'send_user'    => $info->user_id,
            'receive_user' => $info->receive_id,
            'source_type'  => $info->source,
            'data'         => MessageParser::formatTalkMsg([
                'id'         => $info->id,
                'msg_type'   => $info->msg_type,
                'source'     => $info->source,
                'avatar'     => $info->avatar,
                'nickname'   => $info->nickname,
                'user_id'    => $info->user_id,
                'receive_id' => $info->receive_id,
                'created_at' => $info->created_at->toDateTimeString(),
                'file'       => $file,
                'code_block' => $codeBlock,
            ]),
        ];
        if ($info->source === 1) {
            //好友推送
            $redis  = ApplicationContext::getContainer()->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
            $client = $redis->hGet(KernelSocketIO::HASH_UID_TO_SID_PREFIX, (string)$info->receive_id);
            $socketIoService->push($redis->hGet(KernelSocketIO::HASH_UID_TO_SID_PREFIX, (string)$info->user_id), WsMessage::EVENT_CHAT_MESSAGE, $data);
        } else {
            $client = 'room' . $info->receive_id;
        }
        $socketIoService->push($client, WsMessage::EVENT_CHAT_MESSAGE, $data);
    }
}
