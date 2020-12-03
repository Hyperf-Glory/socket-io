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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Context;
use Phper666\JWTAuth\Exception\JWTException;
use Phper666\JWTAuth\Exception\TokenValidException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{

    private $stdoutLogger;

    protected $prefix = 'Bearer';

    private $response;

    public function __construct(StdoutLoggerInterface $logger, Response $response)
    {
        $this->stdoutLogger = $logger;
        $this->response     = $response;
    }

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
            $token = $request->getHeader('Authorization')[0] ?? '';
            if (empty($token)) {
                $token = $this->prefix . ' ' . ($request->getQueryParams()['token'] ?? '');
            }
            $token = ucfirst($token);
            $arr   = explode($this->prefix . ' ', $token);
            $token = $arr[1] ?? '';
            if (($token !== '') && di(InterfaceUserService::class)->checkToken($token)) {
                $request = $this->setRequestContext($token);
                return $handler->handle($request);
            }
            if (!$isValidToken) {
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

    protected function isAuth(ServerRequestInterface $request) : bool
    {
        return true;
    }

    /**
     * @param string $token
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    private function setRequestContext(string $token) : ServerRequestInterface
    {
        $userData = di(InterfaceUserService::class)->decodeToken($token);
        $uid      = $userData['cloud_uid'] ?? 0;
        $rpcUser  = di(InterfaceUserService::class);
        $user     = $rpcUser->get($uid);
        $request  = Context::get(ServerRequestInterface::class);
        $request  = $request->withAttribute('user', $user);
        Context::set(ServerRequestInterface::class, $request);
        return $request;
    }
}
