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
use Hyperf\Contract\StdoutLoggerInterface;
use Phper666\JWTAuth\Exception\TokenValidException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpAuthMiddleware implements MiddlewareInterface
{
    private $stdoutLogger;

    protected $prefix = 'Bearer';

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->stdoutLogger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $isValidToken = false;
        try {
            $token = $request->getHeader('Authorization')[0] ?? '';
            if (empty($token)) {
                $token = $this->prefix . ' ' . ($request->getQueryParams()['token'] ?? '');
            }
            $token = ucfirst($token);
            $arr   = explode($this->prefix . ' ', $token);
            $token = $arr[1] ?? '';

            if (($token !== '') && di(InterfaceUserService::class)->checkToken($token)) {
                $userData = di(InterfaceUserService::class)->decodeToken($token);
                $uid      = $userData['cloud_uid'] ?? 0;
                $rpcUser  = di(InterfaceUserService::class);
                $user     = $rpcUser->get($uid);
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
