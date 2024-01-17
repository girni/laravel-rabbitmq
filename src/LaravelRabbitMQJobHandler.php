<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ;

use Girni\LaravelRabbitMQ\Consumer\ConsumerInterface;
use Girni\LaravelRabbitMQ\Exception\ConsumerNotFoundException;
use Girni\LaravelRabbitMQ\Message\MessageFactory;
use Girni\LaravelRabbitMQ\Handler\PayloadHandler;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob;

class LaravelRabbitMQJobHandler extends RabbitMQJob
{
    /**
     * @return void
     * @throws ConsumerNotFoundException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function fire(): void
    {
        $payload = $this->payload();

        if ((isset($payload['displayName']) && $payload['displayName'] === $payload['producer']) || null === $payload['producer']) {
            parent::fire();

            return;
        }

        $messageFactory = \resolve(MessageFactory::class);
        $consumer = $this->findConsumer($payload['producer']);

        ($this->instance = $consumer->handle($messageFactory->create($consumer, $payload['messageData'])));

        $this->delete();
    }

    public function payload(): array
    {
        $payloadHandler = \resolve(PayloadHandler::class);
        $payload = $payloadHandler->handle(parent::payload());
        return $payload;
    }

    private function findConsumer(string $producer): ConsumerInterface
    {
        $consumers = $this->container->get(ConsumerRegistry::class)->getConsumers();

        /** @var ConsumerInterface $consumer */
        foreach ($consumers as $consumer) {
            if ($consumer->producer() === $producer) {
                return $consumer;
            }
        }

        throw ConsumerNotFoundException::unableToFindConsumerForGivenProducer($producer);
    }
}
