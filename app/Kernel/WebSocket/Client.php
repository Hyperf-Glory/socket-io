<?php
declare(strict_types = 1);

namespace App\Kernel\WebSocket;

use App\Kernel\WebSocket\Exception\InvalidWebSocketConnectionException;
use App\Kernel\WebSocket\Pool\PoolFactory;
use Hyperf\Utils\Context;

class Client
{
    /**
     * @var PoolFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $poolName = 'default';

    public function __construct(PoolFactory $factory)
    {
        $this->factory = $factory;
    }

    public function __call($name, $arguments)
    {
        // Get a connection from coroutine context or connection pool.
        $hasContextConnection = Context::has($this->getContextKey());
        $connection           = $this->getConnection($hasContextConnection);

        try {
            $connection = $connection->getConnection();
            // Execute the command with the arguments.
            $result = $connection->{$name}(...$arguments);
        }
        finally {
            // Release connection.
            if (!$hasContextConnection) {
                $connection->release();
            }
        }

        return $result;
    }

    /**
     * Get a connection from coroutine context, or from redis connectio pool.
     *
     * @param mixed $hasContextConnection
     */
    private function getConnection($hasContextConnection) : ClientConnection
    {
        $connection = NULL;
        if ($hasContextConnection) {
            $connection = Context::get($this->getContextKey());
        }
        if (!$connection instanceof ClientConnection) {
            $pool       = $this->factory->getPool($this->poolName);
            $connection = $pool->get();
        }
        if (!$connection instanceof ClientConnection) {
            throw new InvalidWebSocketConnectionException('The connection is not a valid WebSocketClientConnection.');
        }
        return $connection;
    }

    /**
     * The key to identify the connection object in coroutine context.
     */
    private function getContextKey() : string
    {
        return sprintf('websocket.connection.%s', $this->poolName);
    }
}

