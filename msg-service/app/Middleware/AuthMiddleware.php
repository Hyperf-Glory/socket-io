<?php
declare(strict_types = 1);

namespace App\Milddleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return  $handler->handle($request);
    }

    protected function isAuth(ServerRequestInterface $request) : bool
    {
        return true;
    }
}

