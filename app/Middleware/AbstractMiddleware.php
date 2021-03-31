<?php
declare(strict_types = 1);

namespace App\Middleware;

use App\JsonRpc\Contract\InterfaceUserService;
use App\Kernel\Http\Response;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;
use function Han\Utils\app;

abstract class AbstractMiddleware
{
    protected string $prefix = 'Bearer';

    protected StdoutLoggerInterface $stdoutLogger;

    protected ResponseInterface $response;

    protected Response $httpResponse;

    final public function __construct(StdoutLoggerInterface $logger, ResponseInterface $response, Response $httpResponse)
    {
        $this->stdoutLogger = $logger;
        $this->response     = $response;
        $this->httpResponse = $httpResponse;
    }

    protected function setRequestContext(string $token) : ServerRequestInterface
    {
        /**
         * @var InterfaceUserService $rpcService
         */
        $rpcService = app(InterfaceUserService::class);
        $userData   = $rpcService->decodeToken($token);
        $uid        = $userData['cloud_uid'] ?? 0;
        $user       = $rpcService->get($uid);
        $request    = Context::get(ServerRequestInterface::class);
        $request    = $request->withAttribute('user', $user);
        Context::set(ServerRequestInterface::class, $request);
        return $request;
    }
}
