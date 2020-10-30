<?php
declare(strict_types = 1);

namespace App\Exception\Handler;

use App\Exception\HandshakeException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HandshakeExceptionHandler extends ExceptionHandler
{

    private $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if ($throwable instanceof HandshakeException) {
            $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            $this->logger->error($throwable->getTraceAsString());
            return $response->withHeader('Server', 'Cloud')->withStatus(401)->withBody(new SwooleStream($throwable->getMessage()));
        }
    }

    public function isValid(Throwable $throwable) : bool
    {
        return true;
    }
}


