<?php
declare(strict_types = 1);

namespace App\SocketIO;

use Hyperf\Redis\RedisFactory;
use Hyperf\WebSocketServer\Context;
use Hyperf\WebSocketServer\Context as WsContext;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;
use Hyperf\SocketIOServer\SocketIO as HyperfSocketIO;
use function Han\Utils\app;

class SocketIO extends HyperfSocketIO
{
    public const HASH_UID_TO_SID_PREFIX = 'hash.socket_user.uid_sid';

    public const HASH_SID_TO_UID_PREFIX = 'hash.socket_user.sid_uid';

    public function onOpen($server, Request $request) : void
    {
        $user = $this->getCoContextRequest()->getAttribute('user');
        WsContext::set('user', array_merge(
            ['user' => $user],
            ['sid' => $this->sidProvider->getSid($request->fd)]
        ));
        #判断用户是否在其它地方登录
        $redis    = app(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $uid      = $user['cloud_uid'];
        $isOnline = $sid = $redis->hGet(self::HASH_UID_TO_SID_PREFIX, (string)$uid);
        $redis->multi();
        if ($sid) {
            #解除之前的关系
            $redis->hDel(self::HASH_UID_TO_SID_PREFIX, (string)$uid);
            $redis->hDel(self::HASH_SID_TO_UID_PREFIX, $sid);
            $this->to($sid)->emit('leave', '您的账号在其他地方登录,请注意是否是账号信息被泄漏,请及时更改密码!');
        }
        unset($sid);
        $sid = $this->sidProvider->getSid($request->fd);
        # 绑定用户与fd该功能
        $redis->hSet(self::HASH_UID_TO_SID_PREFIX, (string)$uid, $sid);
        $redis->hSet(self::HASH_SID_TO_UID_PREFIX, $sid, (string)$uid);
        $redis->exec();

        #TODO 绑定聊天群
        $groups = [];
        if ($groups) {
            foreach ($groups as $group) {
                $this->getAdapter()->add(
                    $this->sidProvider->getSid($request->fd),
                    'room' . $group
                );
            }
        }
        if (!$isOnline) {
            #TODO 获取所有好友的用户ID
            $uids = [];
            foreach ($uids as $friend) {
                $this->to($redis->hGet(self::HASH_UID_TO_SID_PREFIX, (string)$friend->uid))->emit('login_notify', ['user_id' => $uid, 'remark' => $friend->remark, 'status' => 1, 'notify' => '好友上线通知...']);
            }
        }
        parent::onOpen($server, $request); // TODO: Change the autogenerated stub
    }

    public function onClose($server, int $fd, int $reactorId) : void
    {
        /**
         * @var array $user
         */
        $user = WsContext::get('user');
        if (empty($user)) {
            return;
        }

        $redis    = app(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $sidCache = $redis->hGet(self::HASH_UID_TO_SID_PREFIX, (string)$user['user']['id']);

        if ($sidCache === $user['sid']) {
            #将fd 退出所有聊天室
            $redis->hDel(self::HASH_UID_TO_SID_PREFIX, (string)$user['user']['id']);
            $redis->hDel(self::HASH_SID_TO_UID_PREFIX, $user['sid']);
        }

        $this->getAdapter()->del($user['sid']);
        WsContext::destroy('user');
        #TODO 获取所有好友的用户ID
        $uids = [];
        foreach ($uids as $friend) {
            #TODO 好友离线通知
            $this->to($redis->hGet(self::HASH_UID_TO_SID_PREFIX, (string)$friend->uid))->emit('quit_notify', [
                'user_id' => $user['user']['id'],
                'remark'  => $friend->remark,
                'status'  => 0,
                'notify'  => '好友离线通知...',
            ]);
        }
        parent::onClose($server, $fd, $reactorId);
    }

    private function getCoContextRequest() : ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class);
    }
}