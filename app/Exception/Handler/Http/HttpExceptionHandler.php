<?php
declare(strict_types = 1);
namespace App\Exception\Handler\Http;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{

    private StdoutLoggerInterface $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, \Psr\Http\Message\ResponseInterface $response)
    {
        // TODO: Implement handle() method.
    }

    public function isValid(Throwable $throwable) : bool
    {
        // TODO: Implement isValid() method.
    }
}