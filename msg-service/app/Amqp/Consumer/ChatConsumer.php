<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use App\Kernel\WebSocket\ClientFactory;
use Hyperf\Amqp\Result;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @Consumer(exchange="hyperf", routingKey="hyperf", queue="hyperf", name ="ChatConsumer", nums=1)
 */
class ChatConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): string
    {
        return Result::ACK;
    }
}
