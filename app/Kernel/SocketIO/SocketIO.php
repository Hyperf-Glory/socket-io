<?php
namespace App\Kernel;

use App\Component\ClientManager;
use App\JsonRpc\Contract\InterfaceUserService;
use App\Log;

use Hyperf\Redis\RedisFactory;
use Hyperf\WebSocketServer\Context as WsContext;
use Phper666\JWTAuth\Exception\TokenValidException;
use Phper666\JWTAuth\Util\JWTUtil;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Hyperf\Di\Annotation\Inject;

class SocketIO extends \Hyperf\SocketIOServer\SocketIO
{
    protected $pingTimeout = 2000;

    protected $pingInterval = 10000; //心跳间隔6秒

    protected $clientCallbackTimeout = 2000;

    public const HASH_UID_TO_FD_PREFIX = 'hash.socket_user';

    /**
     * @Inject
     * @var \Phper666\JWTAuth\JWT
     */
    protected $jwt;

    /**
     * @Inject()
     * @var \App\Service\UserService
     */
    protected $userService;

    /**
     * @Inject()
     * @var \App\Service\UserFriendService
     */
    protected $userFriendService;

    /**
     * @param Response|\Swoole\WebSocket\Server $server
     * @param \Swoole\Http\Request              $request
     */
    public function onOpen($server, Request $request) : void
    {
        try {
            $isValidToken = false;
            $token        = $request->header['Authorization'] ?? '';
            if (strlen($token) > 0) {
                $token = JWTUtil::handleToken($token);
                if ($token !== false && $this->jwt->checkToken($token)) {
                    $isValidToken = true;
                }
            }
            if (!$isValidToken) {
                throw new TokenValidException('Token authentication does not pass', 401);
            }
        } catch (\Throwable $throwable) {
            $this->stdoutLogger->error(sprintf('[%s] [%s]', $throwable->getMessage(), $throwable->getCode()));
            $server->close($request->fd);
            return;
        }

        $userData = $this->jwt->getParserData($token);
        $uid      = $userData['uid'] ?? 0;
        $rpcUser  = di(InterfaceUserService::class);
        $user     = $rpcUser->get($uid);
        //TODO 建立json-rpc客户端获取用户详细信息
        WsContext::set('user', array_merge($user,
            ['sid' => $this->sidProvider->getSid($request->fd)]));
        //判断用户是否在其它地方登录
        $redis    = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $isOnline = $sid = $redis->hGet(self::HASH_UID_TO_FD_PREFIX, (string)$uid);
        $redis->multi();
        if ($sid) {
            //解除之前的关系
            $redis->hDel(self::HASH_UID_TO_FD_PREFIX, (string)$uid);
            $this->to($sid)->emit('leave', '您的账号在其他地方登录,请注意是否是账号信息被泄漏,请及时更改密码!');
        }
        // 绑定用户与fd该功能
        $redis->hSet(self::HASH_UID_TO_FD_PREFIX, (string)$uid, $this->sidProvider->getSid($request->fd));
        $redis->exec();

        // 绑定聊天群
        $groups = $this->userService->getUserGroupIds($uid);
        if ($groups) {
            foreach ($groups as $group) {
                $this->getAdapter()->add(
                    $this->sidProvider->getSid($request->fd),
                    'room' . (string)$group);
            }
        }
        if (!$isOnline) {
            //获取所有好友的用户ID
            $uids       = $this->userFriendService->getFriends($uid);
            $friendSids = [];//所有好友的客户端socketid(sid)
            foreach ($uids as $friend) {
                $friendSids = array_push($friendSids, $redis->hGet(self::HASH_UID_TO_FD_PREFIX, (string)$friend));
            }
            //推送好友上线通知
            if ($friendSids) {
                $this->to($sid)->emit('login_notify', $friendSids, ['user_id' => $uid, 'status' => 1, 'notify' => '好友上线通知...']);
            }
        }
        // 绑定聊天群
        parent::onOpen($server, $request);
    }

    /**
     * @param \Swoole\Http\Response|\Swoole\Server $server
     * @param int                                  $fd
     * @param int                                  $reactorId
     */
    public function onClose($server, int $fd, int $reactorId) : void
    {
        /**
         * @var array $user
         */
        $user = WsContext::get('user');

        // 获取客户端对应的用户ID
        // 清除用户绑定信息
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $redis->hDel(self::HASH_UID_TO_FD_PREFIX, (string)$user['id']);
        // 将fd 退出所有聊天室
        $this->getAdapter()->del($user['sid']);
        WsContext::destroy('user');
        // 判断用户是否多平台登录
        parent::onClose($server, $fd, $reactorId);
    }
}
