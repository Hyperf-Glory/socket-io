<?php
declare(strict_types = 1);

namespace App\Exception\Handler\Rpc;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class RpcExceptionHandler extends ExceptionHandler
{

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // TODO: Implement handle() method.
    }

    public function isValid(Throwable $throwable) : bool
    {
        // TODO: Implement isValid() method.
    }
}
