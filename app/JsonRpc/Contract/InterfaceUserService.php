<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\JsonRpc\Contract;

use App\Constants\User;

interface InterfaceUserService
{
    public function register(string $mobile, string $password, string $smsCode, string $nickname);

    public function login(string $mobile, string $password);

    public function logout(string $token);

    public function sendVerifyCode(string $mobile, string $type = User::REGISTER);

    public function forgetPassword(string $mobile, string $smsCode, string $password);

    public function get(int $uid): ?array;

    public function checkToken(string $token);

    public function decodeToken(string $token);
}
