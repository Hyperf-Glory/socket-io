<?php
declare(strict_types = 1);

namespace App\SocketIO;

use App\Component\MessageParser;
use Hyperf\Utils\Coroutine;
use Hyperf\Contract\StdoutLoggerInterface;

class SocketIOService
{
    protected \Hyperf\SocketIOServer\SocketIO $socketIO;

    protected StdoutLoggerInterface $logger;

    final public function __construct(\Hyperf\SocketIOServer\SocketIO $socketIO, StdoutLoggerInterface $logger)
    {
        $this->socketIO = $socketIO;
        $this->logger   = $logger;
    }

    /**
     * @param        $room
     * @param string $event
     * @param        ...$data
     *
     * @return \Hyperf\SocketIOServer\Emitter\Future|void
     */
    public function push($room, string $event, ...$data)
    {
        Coroutine::create(function () use ($room, $event, $data)
        {
            $this->logger->info(sprintf('SocketIO Push:[%s] Event:[%s] Message:%s', $room, $event, MessageParser::encode($data)));
        });
        return $this->socketIO->to($room)->emit($event, $data);
    }
}
