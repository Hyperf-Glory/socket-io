<?php
declare(strict_types = 1);

namespace App\SocketIO\Proxy;

interface ProxyInterface
{
    /**
     * @param int|array $record
     *
     * @return mixed
     */
    public function process($record);
}
