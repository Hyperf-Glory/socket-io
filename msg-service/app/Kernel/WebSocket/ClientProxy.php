<?php
declare(strict_types = 1);

namespace App\Kernel\WebSocket;

use App\Kernel\WebSocket\Pool\PoolFactory;

class  ClientProxy extends Client
{
    protected $poolName;

    public function __construct(PoolFactory $factory, string $pool)
    {
        parent::__construct($factory);

        $this->poolName = $pool;
    }

    public function __call($name, $arguments)
    {
        return parent::__call($name, $arguments);
    }

}

