<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ;

use Girni\LaravelRabbitMQ\Consumer\ConsumerInterface;
use Girni\LaravelRabbitMQ\Exception\InvalidConsumerException;

final class ConsumerRegistry
{
    /**
     * @var ConsumerInterface[]
     */
    private array $consumers = [];

    public function registerConsumer(string $consumer): void
    {
        $consumer = \resolve($consumer);

        if (!$consumer instanceof ConsumerInterface) {
            throw new InvalidConsumerException(\sprintf('Consumer must implement %s', ConsumerInterface::class));
        }

        $this->consumers[] = $consumer;
    }

    public function registerConsumers(array $consumers): void
    {
        foreach ($consumers as $consumer) {
            $this->registerConsumer($consumer);
        }
    }

    /**
     * @var ConsumerInterface[]
     */
    public function getConsumers(): array
    {
        return $this->consumers;
    }
}
