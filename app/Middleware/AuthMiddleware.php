<?php

declare(strict_types = 1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Milddleware;

use App\JsonRpc\Contract\InterfaceUserService;
use App\Kernel\Http\Response;
use Phper666\JWTAuth\Exception\TokenValidException;
use Phper666\JWTAuth\JWT;
use Phper666\JWTAuth\Util\JWTUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $isValidToken = false;
        try {
            $token = $request->get['token'] ?? '';
            if (($token !== '') && di(InterfaceUserService::class)->checkToken($token)) {
                return $handler->handle($request);
            }
            if (!$isValidToken) {
                throw new TokenValidException('Token authentication does not pass', 401);
            }
        } catch (\Throwable $throwable) {
            $this->stdoutLogger->error(sprintf('[%s] [%s]', $throwable->getMessage(), $throwable->getCode()));
        }
        throw new TokenValidException('Token authentication does not pass', 401);
    }

    protected function isAuth(ServerRequestInterface $request) : bool
    {
        return true;
    }
}
