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

use App\Constants\ErrorCode;
use App\JsonRpc\Contract\InterfaceUserService;
use App\Kernel\Http\Response;
use Hyperf\Utils\Context;
use Phper666\JWTAuth\Exception\TokenValidException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpAuthMiddleware implements MiddlewareInterface
{

    private $response;

    protected $prefix = 'Bearer';

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $isValidToken = false;
        $token        = $request->getHeader('Authorization')[0] ?? '';
        if (empty($token)) {
            $token = $this->prefix . ' ' . ($request->getQueryParams()['token'] ?? '');
        }
        if (strlen($token) > 0) {
            $token = ucfirst($token);
            $arr   = explode($this->prefix . ' ', $token);
            $token = $arr[1] ?? '';
            try {
                if (strlen($token) > 0 && di(InterfaceUserService::class)->checkToken($token)) {
                    $isValidToken = true;
                }
            } catch (Throwable $e) {
                throw new \Exception(ErrorCode::AUTH_ERROR);
            }
        }

        if ($isValidToken) {
            $jwtData = di(InterfaceUserService::class)->decodeToken($token);
            $user    = di(InterfaceUserService::class)->get($jwtData['id']);
            if (empty($user)) {
                throw new \Exception(ErrorCode::AUTH_ERROR);
            }
            $request = Context::get(ServerRequestInterface::class);
            $request = $request->withAttribute('user', $user);
            Context::set(ServerRequestInterface::class, $request);

            return $handler->handle($request);
            // 根据具体业务判断逻辑走向，这里假设用户携带的token有效
        }
        throw new TokenValidException('Token authentication does not pass', 401);
    }

    protected function isAuth(ServerRequestInterface $request) : bool
    {
        return true;
    }
}
