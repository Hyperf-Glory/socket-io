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
namespace App\JsonRpc\Contract;

use App\Constants\User;
use App\Kernel\JsonRpc\RpcResponse;

interface InterfaceUserService
{
    public function register(string $mobile, string $password, string $smsCode, string $nickname) : RpcResponse;

    public function login(string $mobile, string $password) : RpcResponse;

    public function logout(string $token);

    public function sendVerifyCode(string $mobile, string $type = User::REGISTER) : RpcResponse;

    public function forgetPassword(string $mobile, string $smsCode, string $password) : RpcResponse;

    public function get(int $uid) : ?array;

    public function checkToken(string $token);

    public function decodeToken(string $token);
}
