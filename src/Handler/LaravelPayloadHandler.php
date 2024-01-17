<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Handler;

use Girni\LaravelRabbitMQ\Handler\HandlerInterface;

final class LaravelPayloadHandler implements HandlerInterface
{
    public function handle(array $payload): array
    {
        $job = \unserialize($payload['data']['command']);
        $producerName = $payload['displayName'];
        $messageData = $payload['data'];

        if (\is_object($job)) {
            $job = (array) $job;
            $producerName = $job['name'] ?? null;
            $messageData = $job['data'] ?? $payload['data'];
        }

        return \array_merge($payload, ['producer' => $producerName, 'messageData' => $messageData]);
    }
}
