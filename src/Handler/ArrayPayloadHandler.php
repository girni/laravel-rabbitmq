<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Handler;

use Girni\LaravelRabbitMQ\Handler\HandlerInterface;

final class ArrayPayloadHandler implements HandlerInterface
{
    public function handle(array $payload): array
    {
        $job = \Arr::get($payload, 'job', null);
        if (!$job || !is_string($job)) {
           \Log::debug('Unable to process message. Message doesn\'t contain `producer` key.', ['payload' => $payload]);
           throw new \InvalidArgumentException("Unable to process message. Message doesn\'t contain `producer` key.");
        }

        return \array_merge($payload, ['producer' => $payload['job'], 'messageData' => $payload['data']]);
    }
}
