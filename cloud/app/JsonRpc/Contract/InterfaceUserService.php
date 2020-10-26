<?php
declare(strict_types = 1);

namespace App\JsonRpc\Contract;

use App\Constants\User;

interface  InterfaceUserService
{
    public function register(string $mobile, string $password, string $smsCode, string $nickname);

    public function login(string $mobile, string $password);

    public function logout(string $token);

    public function sendVerifyCode(string $mobile, string $type = User::REGISTER);

    public function forgetPassword(string $mobile, string $smsCode, string $password);
}
