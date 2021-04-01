<?php
declare(strict_types = 1);

namespace App\Listener;

use Hyperf\Contract\StdoutLoggerInterface;

abstract class AbstractProcessListener
{
    protected StdoutLoggerInterface $logger;

    final public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
