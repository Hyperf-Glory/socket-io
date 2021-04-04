<?php

declare(strict_types = 1);
/**
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/Hyperf-Glory/socket-io
 */
namespace App\JsonRpc;

use App\Component\Sms;
use App\Constants\User;
use App\Exception\RpcException;
use App\Helper\ValidateHelper;
use App\JsonRpc\Contract\InterfaceUserService;
use App\Kernel\JsonRpc\Response;
use App\Model\User as UserModel;
use App\Service\UserService as UserSer;
use Hyperf\Logger\LoggerFactory;
use Hyperf\RpcServer\Annotation\RpcService;
use Phper666\JWTAuth\JWT;
use Psr\Container\ContainerInterface;
use Throwable;
use function Han\Utils\app;

/**
 * Class Cloud.
 * @RpcService(name="UserService", protocol="jsonrpc-tcp-length-check", server="jsonrpc", publishTo="consul")
 */
class UserService implements InterfaceUserService
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var \Phper666\JWTAuth\JWT
     */
    protected JWT $jwt;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \App\Service\UserService
     */
    private UserSer $userService;

    public function __construct(ContainerInterface $container, UserSer $userService, JWT $jwt)
    {
        $this->container   = $container;
        $this->logger      = $container->get(LoggerFactory::class)->get();
        $this->userService = $userService;
        $this->jwt         = $jwt;
    }

    public function register(string $mobile, string $password, string $smsCode, string $nickname) : array
    {
        if (!ValidateHelper::isPhone($mobile)) {
            return Response::fail(null, '手机号格式不正确...');
        }
        if (!app(Sms::class)->check('user_register', $mobile, $smsCode)) {
            return Response::fail(null, '验证码填写错误...');
        }
        $bool = $this->userService->register($mobile, $password, $nickname);
        if ($bool) {
            app(Sms::class)->delCode('user_register', $mobile);
            return Response::success(null, '账号注册成功...');
        }
        return Response::fail(null, '账号注册失败,手机号已被其他(她)人使用...');
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function login(string $mobile, string $password) : array
    {
        /**
         * @var UserModel $user
         */

        if (!($user = UserModel::query()->where('mobile', $mobile)->first())) {
            return Response::fail(null, '登录账号不存在...');
        }
        if (!$this->userService->checkPassword($password, $user->password)) {
            return Response::fail(null, '登录密码错误...');
        }
        $token = $this->jwt->setScene('cloud')->getToken([
            'cloud_uid' => $user->id,
            'nickname'  => $user->nickname,
        ]);
        return Response::success([
            'authorize' => [
                'access_token' => $token,
                'expires_in'   => $this->jwt->getTTL(),
            ],
            'user_info' => [
                'uid'      => $user->id,
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
                'motto'    => $user->motto,
                'gender'   => $user->gender,
            ]
        ], null);
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function logout(string $token) : void
    {
        try {
            $this->jwt->logout($token);
        } catch (Throwable $throwable) {
            throw new RpcException(sprintf('json-rpc logout Error [%s] [%s]', $token, $throwable->getMessage()));
        }
    }

    public function sendVerifyCode(string $mobile, string $type = User::REGISTER) : array
    {
        if (!app(Sms::class)->isUsages($type)) {
            return Response::fail(null, '验证码发送失败...');
        }
        if (!ValidateHelper::isPhone($mobile)) {
            return Response::fail(null, '手机号格式不正确...');
        }
        if ($type === Sms::FORGET_PASSWORD) {
            if (!UserModel::query()->where('mobile', $mobile)->value('id')) {
                return Response::fail(null, '手机号未被注册使用...');
            }
        } elseif ($type === Sms::CHANGE_MOBILE || $type === Sms::USER_REGISTER) {
            if (UserModel::query()->where('mobile', $mobile)->value('id')) {
                return Response::fail(null, '手机号已被他(她)人注册...');
            }
        }
        [$isTrue, $result] = app(Sms::class)->send($type, $mobile);
        if ($isTrue) {
            return Response::success(['sms_code' => $result['data']['code']], null);
        }

        return Response::fail(null, '发送失败...');
    }

    public function forgetPassword(string $mobile, string $smsCode, string $password) : array
    {
        if (empty($smsCode) || empty($password) || !ValidateHelper::isPhone($mobile)) {
            return Response::fail(null, '参数错误...');
        }
        if (!ValidateHelper::checkPassword($password)) {
            return Response::fail(null, '密码格式不正确...');
        }
        if (!di(Sms::class)->check('forget_password', $mobile, $smsCode)) {
            return Response::fail(null, '验证码填写错误...');
        }
        try {
            $bool = $this->userService->resetPassword($mobile, $password);
            if ($bool) {
                app(Sms::class)->delCode('forget_password', $mobile);
            }
            return $bool ? Response::success(null, '重置密码成功...') : Response::success(null, '重置密码失败...');
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('json-rpc forgetPassword fail [%s] [%s]', $mobile, $throwable->getMessage()));
            return ['code' => 0, 'msg' => '重置密码失败...'];
        }
    }

    /**
     * @param int $uid
     *
     * @return null|array
     */
    public function get(int $uid) : ?array
    {
        try {
            $user = $this->userService->get($uid);
            if ($user) {
                return Response::success([
                    'id'       => $user->id,
                    'nickname' => $user->nickname,
                    'avatar'   => $user->avatar,
                ], null);
            }
            return null;
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('json-rpc UserService Error getting user[%s] information', $uid));
        }
        return Response::fail(null, null);
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Throwable
     */
    public function checkToken(string $token) : bool
    {
        try {
            return $this->jwt->checkToken($token);
        } catch (\Throwable $throwable) {
            $this->logger->error(sprintf('json-rpc CheckToken Fail [%s]  [%s]', $token, $throwable->getMessage()));
            return false;
        }
    }

    public function decodeToken(string $token) : array
    {
        return $this->jwt->getParserData($token);
    }
}
