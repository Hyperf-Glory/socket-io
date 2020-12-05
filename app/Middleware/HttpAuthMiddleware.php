<?php

declare(strict_types=1);
/**
 *
 * This file is part of the My App.
 *
 * Copyright CodingHePing 2016-2020.
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/codingheping/hyperf-chat-upgrade
 */
namespace App\Milddleware;

use App\JsonRpc\Contract\InterfaceUserService;
use App\Kernel\Http\Response;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Context;
use Phper666\JWTAuth\Exception\JWTException;
use Phper666\JWTAuth\Exception\TokenValidException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpAuthMiddleware implements MiddlewareInterface
{
    protected $prefix = 'Bearer';

    private $stdoutLogger;

    private $response;

    public function __construct(StdoutLoggerInterface $logger, Response $response)
    {
        $this->stdoutLogger = $logger;
        $this->response = $response;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isValidToken = false;
        try {
            $token = $request->getHeader('Authorization')[0] ?? '';
            if (empty($token)) {
                $token = $this->prefix . ' ' . ($request->getQueryParams()['token'] ?? '');
            }
            $token = ucfirst($token);
            $arr = explode($this->prefix . ' ', $token);
            $token = $arr[1] ?? '';

            if (($token !== '') && di(InterfaceUserService::class)->checkToken($token)) {
                $request = $this->setRequestContext($token);
                return $handler->handle($request);
            }
            if (! $isValidToken) {
                throw new TokenValidException('Token authentication does not pass', 401);
            }
        } catch (\Throwable $throwable) {
            $this->stdoutLogger->error(sprintf('[%s] [%s] [%s] [%s]', $throwable->getMessage(), $throwable->getCode(), $throwable->getLine(), $throwable->getFile()));
            if ($throwable instanceof TokenValidException || $throwable instanceof JWTException) {
                throw new TokenValidException('Token authentication does not pass', 401);
            }
        }

        return $this->response->response()->withHeader('Server', 'Hyperf')->withStatus(500)->withBody(new SwooleStream('Internal Server Error.'));
    }

    private function setRequestContext(string $token): ServerRequestInterface
    {
        $userData = di(InterfaceUserService::class)->decodeToken($token);
        $uid = $userData['cloud_uid'] ?? 0;
        $rpcUser = di(InterfaceUserService::class);
        $user = $rpcUser->get($uid);
        $request = Context::get(ServerRequestInterface::class);
        $request = $request->withAttribute('user', $user);
        Context::set(ServerRequestInterface::class, $request);
        return $request;
    }
}
