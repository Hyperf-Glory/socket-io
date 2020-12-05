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
namespace App\Component;

use App\Kernel\SocketIO as KernelSocketIO;
use App\Model\ChatRecords;
use App\Model\ChatRecordsCode;
use App\Model\ChatRecordsFile;
use App\Model\ChatRecordsForward;
use App\Model\ChatRecordsInvite;
use App\Model\Users;
use Hyperf\Redis\RedisFactory;
use Hyperf\SocketIOServer\SocketIO;
use RuntimeException;

/**
 * Class Proxy.
 * @description  一定要注意该组件不能推送给自己,推送给自己请用$socketio->emit()即可
 */
class Proxy
{
    /**
     * 邀请入群通知  踢出群聊通知  自动退出群聊.
     *
     * @throws \Exception
     */
    public function groupNotify(int $record): void
    {
        /**
         * @var ChatRecords $recordInfo
         */
        $recordInfo = ChatRecords::where('id', $record)->where('source', 2)->first([
            'id',
            'msg_type',
            'user_id',
            'receive_id',
            'created_at',
        ]);
        if (! $recordInfo) {
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

        if (! $notifyInfo) {
            throw new RuntimeException('fail');
        }

        /**
         * @var Users $userInfo
         */
        $userInfo = Users::where('id', $notifyInfo->operate_user_id)->first(['nickname', 'id']);

        $membersIds = explode(',', $notifyInfo->user_ids);

        $io = di(SocketIO::class);
        //推送群聊消息
        $io->to('room' . $recordInfo->receive_id)->emit('chat_message', [
            'send_user' => 0,
            'receive_user' => $recordInfo->receive_id,
            'source_type' => 2,
            'data' => PushMessageHelper::formatTalkMsg([
                'id' => $recordInfo->id,
                'source' => 2,
                'msg_type' => 3,
                'user_id' => 0,
                'receive_id' => $recordInfo->receive_id,
                'invite' => [
                    'type' => $notifyInfo->type,
                    'operate_user' => ['id' => $userInfo->id, 'nickname' => $userInfo->nickname],
                    'users' => Users::select(['id', 'nickname'])->whereIn('id', $membersIds)->get()->toArray(),
                ],
                'created_at' => $recordInfo->created_at,
            ]),
        ]);
    }

    /**
     *  推送好友撤销消息事件.
     *
     * @throws \Exception
     */
    public function revokeRecords(int $record): void
    {
        /**
         * @var ChatRecords $records
         */
        $records = ChatRecords::where('id', $record)->first(['id', 'source', 'user_id', 'receive_id']);
        if (! $records) {
            throw new RuntimeException('数据不存在...');
        }
        $io = di(SocketIO::class);
        //TODO 好友或群聊推送
        if ($records->source === 1) {
            //好友推送
            $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
            $client = $redis->hGet(KernelSocketIO::HASH_UID_TO_FD_PREFIX, (string) $records->receive_id);
        } else {
            $client = 'room' . $records->receive_id;
            //群聊推送
        }
        $io->to($client)->emit('revoke_records', [
            'record_id' => $records->id,
            'source' => $records->source,
            'user_id' => $records->user_id,
            'receive_id' => $records->receive_id,
        ]);
    }

    /**
     * 推送聊天记录转发消息.
     */
    public function forwardChatRecords(array $records): void
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
        $io = di(SocketIO::class);
        /**
         * @var ChatRecords|ChatRecordsForward|Users $record
         */
        foreach ($rows as $record) {
            if ($records->source === 1) {
                //好友推送
                $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
                $client = $redis->hGet(KernelSocketIO::HASH_UID_TO_FD_PREFIX, (string) $record->receive_id);
            } else {
                //群聊推送
                $client = 'room' . $record->receive_id;
            }
            $io->to($client)->emit('revoke_records', [
                'send_user' => $record->user_id,
                'receive_user' => $record->receive_id,
                'source_type' => $record->source,
                'data' => PushMessageHelper::formatTalkMsg([
                    'id' => $record->id,
                    'msg_type' => $record->msg_type,
                    'source' => $record->source,
                    'avatar' => $record->avatar,
                    'nickname' => $record->nickname,
                    'user_id' => $record->user_id,
                    'receive_id' => $record->receive_id,
                    'created_at' => $record->created_at,
                    'forward' => [
                        'num' => substr_count($record->records_id, ',') + 1,
                        'list' => MessageParser::decode($record->text) ?? [],
                    ],
                ]),
            ]);
        }
    }

    /**
     * 根据消息ID推送客户端.
     *
     * @throws \Exception
     */
    public function pushTalkMessage(int $record): void
    {
        /**
         * @var ChatRecords| Users $info
         */
        $info = ChatRecords::leftJoin('users', 'users.id', '=', 'chat_records.user_id')->where('chat_records.id', $record)->first([
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
        if (! $info) {
            throw new RuntimeException('fail');
        }
        $io = di(SocketIO::class);
        $file = [];
        $codeBlock = [];
        if ($info->msg_type === 2) {
            $file = ChatRecordsFile::where('record_id', $info->id)->first(['id', 'record_id', 'user_id', 'file_source', 'file_type', 'save_type', 'original_name', 'file_suffix', 'file_size', 'save_dir']);
            $file = $file ? $file->toArray() : [];
            if ($file) {
                //TODO 处理静态资源(图片,视频)
                $file['file_url'] = config('image_url') . $file['save_dir'];
            }
        } elseif ($info->msg_type === 5) {
            $codeBlock = ChatRecordsCode::where('record_id', $info->id)->first(['record_id', 'code_lang', 'code']);
            $codeBlock = $codeBlock ? $codeBlock->toArray() : [];
        }

        if ($info->source === 1) {
            //好友推送
            $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
            $client = $redis->hGet(KernelSocketIO::HASH_UID_TO_FD_PREFIX, (string) $info->receive_id);
        } else {
            $client = 'room' . $info->receive_id;
        }
        $io->to($client)->emit('chat_message', [
            'send_user' => $info->user_id,
            'receive_user' => $info->receive_id,
            'source_type' => $info->source,
            'data' => MessageParser::formatTalkMsg([
                'id' => $info->id,
                'msg_type' => $info->msg_type,
                'source' => $info->source,
                'avatar' => $info->avatar,
                'nickname' => $info->nickname,
                'user_id' => $info->user_id,
                'receive_id' => $info->receive_id,
                'created_at' => $info->created_at,
                'file' => $file,
                'code_block' => $codeBlock,
            ]),
        ]);
    }
}
