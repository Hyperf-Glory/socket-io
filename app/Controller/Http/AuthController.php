<?php
declare(strict_types = 1);
namespace App\Controller\Http;

use App\Controller\AbstractController;
use App\Request\LoginRequest;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;

class AuthController extends AbstractController
{

    public function register() : ResponseInterface
    {

    }

    public function login(LoginRequest $loginRequest) : ResponseInterface
    {
        $validated = $loginRequest->validated();
        return '1';
    }

    public function logout() : ResponseInterface
    {

    }

    public function sendVerifyCode() : ResponseInterface
    {

    }

    public function forgetPassword() : ResponseInterface
    {

    }
}

