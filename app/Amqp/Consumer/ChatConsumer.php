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
        dump($data);
        $client = $this->container->get(ClientFactory::class)->get('ws1');
        dump($client->push('测试测试'));
        while ($msg = $client->recv(2)){
            dump($msg);
        }

        return Result::ACK;
    }
}
