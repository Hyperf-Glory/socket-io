<?php

declare(strict_types=1);

namespace App\Amqp\Producer;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;

/**
 * @Producer(exchange="hyperf", routingKey="hyperf")
 */
class ChatProducer extends ProducerMessage
{
    public function __construct(string $data)
    {
        $this->payload = $data;
    }
}
