<?php
declare(strict_types = 1);

namespace App\Controller\SocketIO;

use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;

class EventController extends BaseNamespace
{
    public function KeyBoard(Socket $socket, $data) : void
    {
    }

    public function talk(Socket $socket, $data) : void
    {

    }
}
