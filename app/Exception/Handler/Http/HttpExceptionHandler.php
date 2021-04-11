<?php
declare(strict_types = 1);
namespace App\Exception\Handler\Http;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{

    private StdoutLoggerInterface $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response) : ResponseInterface
    {
        return $response;
    }

    public function isValid(Throwable $throwable) : bool
    {
        return true;
    }
}
