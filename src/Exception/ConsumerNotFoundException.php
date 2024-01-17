<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Exception;

use RuntimeException;

class ConsumerNotFoundException extends RuntimeException
{
    public static function unableToFindConsumerForGivenProducer(string $producer): self
    {
        return new self("Consumer not found for producer ($producer)");
    }
}
