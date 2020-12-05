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
namespace App\Kernel;

use App\JsonRpc\Contract\InterfaceUserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\RedisFactory;
use Hyperf\WebSocketServer\Context as WsContext;
use Swoole\Http\Request;

class SocketIO extends \Hyperf\SocketIOServer\SocketIO
{
    public const HASH_UID_TO_FD_PREFIX = 'hash.socket_user';

    protected $pingTimeout = 2000;

    protected $pingInterval = 10000; //心跳间隔6秒

    protected $clientCallbackTimeout = 2000;

    /**
     * @Inject
     * @var \Phper666\JWTAuth\JWT
     */
    protected $jwt;

    /**
     * @Inject
     * @var \App\Service\UserService
     */
    protected $userService;

    /**
     * @Inject
     * @var \App\Service\UserFriendService
     */
    protected $userFriendService;


    public function onOpen($server, Request $request): void
    {
        $token = $request->get['token'] ?? '';
        $userData = di(InterfaceUserService::class)->decodeToken($token);
        $uid = $userData['cloud_uid'] ?? 0;
        $rpcUser = di(InterfaceUserService::class);
        $user = $rpcUser->get($uid);
        if (is_null($user)) {
            $server->close($request->fd);
            return;
        }
        //TODO 建立json-rpc客户端获取用户详细信息
        WsContext::set('user', array_merge(
            ['user' => $user],
            ['sid' => $this->sidProvider->getSid($request->fd)]
        ));
        //判断用户是否在其它地方登录
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $isOnline = $sid = $redis->hGet(self::HASH_UID_TO_FD_PREFIX, (string) $uid);
        $redis->multi();
        if ($sid) {
            //解除之前的关系
            $redis->hDel(self::HASH_UID_TO_FD_PREFIX, (string) $uid);
            $this->to($sid)->emit('leave', '您的账号在其他地方登录,请注意是否是账号信息被泄漏,请及时更改密码!');
        }
        unset($sid);
        $sid = $this->sidProvider->getSid($request->fd);
        // 绑定用户与fd该功能
        $redis->hSet(self::HASH_UID_TO_FD_PREFIX, (string) $uid, $sid);
        $redis->exec();

        // 绑定聊天群
        $groups = $this->userService->getUserGroupIds($uid);
        if ($groups) {
            foreach ($groups as $group) {
                $this->getAdapter()->add(
                    $this->sidProvider->getSid($request->fd),
                    'room' . $group
                );
            }
        }
        if (! $isOnline) {
            //获取所有好友的用户ID
            $uids = $this->userFriendService->getFriends($uid);
            $friendSids = []; //所有好友的客户端socketid(sid)
            foreach ($uids as $friend) {
                $friendSids = array_push($friendSids, $redis->hGet(self::HASH_UID_TO_FD_PREFIX, (string) $friend));
            }
            //推送好友上线通知
            if ($friendSids) {
                $this->to($sid)->emit('login_notify', $friendSids, ['user_id' => $uid, 'status' => 1, 'notify' => '好友上线通知...']);
            }
        }
        // 绑定聊天群
        parent::onOpen($server, $request);
    }


    public function onClose($server, int $fd, int $reactorId): void
    {
        /**
         * @var array $user
         */
        $user = WsContext::get('user');
        if (empty($user)) {
            return;
        }
        // 获取客户端对应的c用户ID
        // 清除用户绑定信息
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $redis->hDel(self::HASH_UID_TO_FD_PREFIX, (string) $user['user']['id']);
        // 将fd 退出所有聊天室
        $this->getAdapter()->del($user['sid']);
        WsContext::destroy('user');
        //获取所有好友的用户ID
        $uids = $this->userFriendService->getFriends($user['user']['id']);
        $friendSids = []; //所有好友的客户端socketid(sid)
        foreach ($uids as $friend) {
            $friendSids = array_push($friendSids, $redis->hGet(self::HASH_UID_TO_FD_PREFIX, (string) $friend));
        }
        //推送好友下线通知
        if ($friendSids) {
            $this->to($user['sid'])->emit('login_notify', $friendSids, [
                'user_id' => $user['id'],
                'status' => 0,
                'notify' => '好友离线通知...',
            ]);
        }
        // 判断用户是否多平台登录
        parent::onClose($server, $fd, $reactorId);
    }
}
