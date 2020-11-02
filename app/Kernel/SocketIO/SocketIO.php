<?php
namespace App\Kernel;

use App\Component\ClientManager;
use App\Log;

use Hyperf\Redis\RedisFactory;
use Hyperf\SocketIOServer\Collector\SocketIORouter;
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
        $userData = $this->jwt->getParserData($token);
        $uid      = $userData['uid'] ?? 0;
        //判断用户是否在其它地方登录
        $redis = di(RedisFactory::class)->get(env('CLOUD_REDIS'));
        $redis->multi();
        $isOnline = $sid = $redis->hGet(self::HASH_UID_TO_FD_PREFIX, (string)$uid);
        if ($sid) {
            //解除之前的关系
            $redis->hDel(self::HASH_UID_TO_FD_PREFIX, (string)$uid);
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
            $uids = $this->userFriendService->getFriends($uid);
            $ffds = [];//所有好友的客户端ID
            foreach ($uids as $friend) {

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

        // 获取客户端对应的用户ID

        // 清除用户绑定信息

        // 将fd 退出所有聊天室

        // 判断用户是否多平台登录
        parent::onClose($server, $fd, $reactorId);
    }
}
