<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Message;

use Girni\LaravelRabbitMQ\Consumer\ConsumerInterface;
use Girni\LaravelRabbitMQ\Consumer\HasCustomMessage;

final class MessageFactory
{
    public function create(ConsumerInterface $consumer, array $messageData): MessageInterface
    {
        if ($consumer instanceof HasCustomMessage) {
            $messageClass = $consumer->message();

            return $messageClass::fromArray($messageData);
        }

        return BaseMessage::fromArray($messageData);
    }
}
