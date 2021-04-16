<?php
declare(strict_types = 1);

namespace App\SocketIO\Proxy;

use App\Constants\WsMessage;
use App\Model\ChatRecord;
use App\SocketIO\SocketIO as KernelSocketIO;
use App\SocketIO\SocketIOService;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;
use RuntimeException;

/**
 * 推送好友撤销消息事件.
 * Class RevokeRecord
 * @package App\SocketIO\Proxy
 */
class RevokeRecord implements ProxyInterface
{

    public function process($record)
    {
        $socketIoService = make(SocketIOService::class);
        /**
         * @var ChatRecord $records
         */
        $records = ChatRecord::where('id', $record)->first(['id', 'source', 'user_id', 'receive_id']);
        if (!$records) {
            throw new RuntimeException('数据不存在...');
        }

        $data  = [
            'record_id'  => $records->id,
            'source'     => $records->source,
            'user_id'    => $records->user_id,
            'receive_id' => $records->receive_id,
        ];
        $redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get(env('CLOUD_REDIS'));
        if ($records->source === 1) {
            //好友推送
            $client = $redis->hGet(KernelSocketIO::HASH_UID_TO_SID_PREFIX, (string)$records->receive_id);
        } else {
            //群聊推送
            $client = 'room' . $records->receive_id;
        }
        $socketIoService->push($client, WsMessage::EVENT_REVOKE_RECORDS, $data);
        $socketIoService->push($redis->hGet(KernelSocketIO::HASH_UID_TO_SID_PREFIX, (string)$records->user_id), WsMessage::EVENT_CHAT_MESSAGE, $data);
    }
}
