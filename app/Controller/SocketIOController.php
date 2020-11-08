<?php
declare(strict_types = 1);

namespace App\Controller;

use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;

/**
 * Class SocketIOController
 * @package App\Controller
 * @SocketIONamespace("/socket-io")
 */
class SocketIOController extends BaseNamespace
{
    /**
     * @param \Hyperf\SocketIOServer\Socket $socket
     * @param                               $data
     * @Event("event")
     */
    public function onEvent(\Hyperf\SocketIOServer\Socket $socket, $data)
    {
        dump($data);
    }
}
