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
namespace App\Middleware;

use App\JsonRpc\Contract\InterfaceUserService;
use Hyperf\Contract\StdoutLoggerInterface;
use Phper666\JWTAuth\Exception\TokenValidException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function Han\Utils\app;

class SocketIOAuthMiddleware extends AbstractMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        //通过 isAuth 方法拦截握手请求并实现权限检查
        if (!$this->isAuth($request)) {
            return $this->response->raw('Forbidden');
        }
        $request = $this->setRequestContext($request->getQueryParams()['token'] ?? '');
        return $handler->handle($request);
    }

    protected function isAuth(ServerRequestInterface $request) : bool
    {
        try {
            $isValidToken = false;
            $token        = $request->getQueryParams()['token'] ?? '';
            if (($token !== '') && app(InterfaceUserService::class)->checkToken($token)) {
                return true;
            }
            if (!$isValidToken) {
                throw new TokenValidException('Token authentication does not pass', 401);
            }
        } catch (\Throwable $throwable) {
            $this->stdoutLogger->error(sprintf('[%s] [%s]', $throwable->getMessage(), $throwable->getCode()));
            return false;
        }
        return false;
    }
}
