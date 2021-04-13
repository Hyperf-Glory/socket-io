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
use App\Kernel\JsonRpc\ResponseHelper;
use App\Kernel\JsonRpc\RpcResponse;
use App\Model\User as UserModel;
use App\Resource\Users;
use App\Service\UserService as UserSer;
use Hyperf\Logger\LoggerFactory;
use Hyperf\RpcServer\Annotation\RpcService;
use Phper666\JWTAuth\JWT;
use Psr\Container\ContainerInterface;
use Throwable;

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

    public function register(string $mobile, string $password, string $smsCode, string $nickname) : RpcResponse
    {
        if (!ValidateHelper::isPhone($mobile)) {
            return ResponseHelper::fail(null, '手机号格式不正确...');
        }
        if (!$this->container->get(Sms::class)->check('user_register', $mobile, $smsCode)) {
            return ResponseHelper::fail(null, '验证码填写错误...');
        }
        $bool = $this->userService->register($mobile, $password, $nickname);
        if ($bool) {
            $this->container->get(Sms::class)->delCode('user_register', $mobile);
            return ResponseHelper::success(null, '账号注册成功...');
        }
        return ResponseHelper::fail(null, '账号注册失败,手机号已被其他(她)人使用...');
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function login(string $mobile, string $password) : RpcResponse
    {
        /**
         * @var UserModel $user
         */

        if (!($user = UserModel::query()->where('mobile', $mobile)->first())) {
            return ResponseHelper::fail(null, '登录账号不存在...');
        }
        if (!$this->userService->checkPassword($password, $user->password)) {
            return ResponseHelper::fail(null, '登录密码错误...');
        }
        $token = $this->jwt->setScene('cloud')->getToken([
            'cloud_uid' => $user->id,
            'nickname'  => $user->nickname,
        ]);
        return ResponseHelper::success([
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

    public function sendVerifyCode(string $mobile, string $type = User::REGISTER) : RpcResponse
    {
        if (!$this->container->get(Sms::class)->isUsages($type)) {
            return ResponseHelper::fail(null, '验证码发送失败...');
        }
        if (!ValidateHelper::isPhone($mobile)) {
            return ResponseHelper::fail(null, '手机号格式不正确...');
        }
        if ($type === Sms::FORGET_PASSWORD) {
            if (!UserModel::query()->where('mobile', $mobile)->value('id')) {
                return ResponseHelper::fail(null, '手机号未被注册使用...');
            }
        } elseif ($type === Sms::CHANGE_MOBILE || $type === Sms::USER_REGISTER) {
            if (UserModel::query()->where('mobile', $mobile)->value('id')) {
                return ResponseHelper::fail(null, '手机号已被他(她)人注册...');
            }
        }
        [$isTrue, $result] = $this->container->get(Sms::class)->send($type, $mobile);
        if ($isTrue) {
            return ResponseHelper::success(['sms_code' => $result['data']['code']], null);
        }

        return ResponseHelper::fail(null, '发送失败...');
    }

    public function forgetPassword(string $mobile, string $smsCode, string $password) : RpcResponse
    {
        if (empty($smsCode) || empty($password) || !ValidateHelper::isPhone($mobile)) {
            return ResponseHelper::fail(null, '参数错误...');
        }
        if (!ValidateHelper::checkPassword($password)) {
            return ResponseHelper::fail(null, '密码格式不正确...');
        }
        if (!di(Sms::class)->check('forget_password', $mobile, $smsCode)) {
            return ResponseHelper::fail(null, '验证码填写错误...');
        }
        try {
            $bool = $this->userService->resetPassword($mobile, $password);
            if ($bool) {
                $this->container->get(Sms::class)->delCode('forget_password', $mobile);
            }
            return $bool ? ResponseHelper::success(null, '重置密码成功...') : ResponseHelper::success(null, '重置密码失败...');
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('json-rpc forgetPassword fail [%s] [%s]', $mobile, $throwable->getMessage()));
            return ResponseHelper::fail(null, '重置密码失败...');
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
            /**
             * @var userModel $user
             */
            $user = $this->userService->get($uid);
            if ($user) {
                return ResponseHelper::success((array)new Users(
                    $user)
                    , null);
            }
            return null;
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('json-rpc UserService Error getting user[%s] information', $uid));
        }
        return ResponseHelper::fail(null, null);
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
