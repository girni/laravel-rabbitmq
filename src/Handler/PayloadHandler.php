<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Handler;

use Girni\LaravelRabbitMQ\Handler\ArrayPayloadHandler;
use Girni\LaravelRabbitMQ\Handler\HandlerInterface;
use Girni\LaravelRabbitMQ\Handler\LaravelPayloadHandler;

final class PayloadHandler
{
    public function handle(array $payload): array
    {
        return $this->findHandler($payload)->handle($payload);
    }

    public function findHandler(array $payload): HandlerInterface
    {
        try {
            $laravelPayloadHandler = \resolve(LaravelPayloadHandler::class);
            $payload = $laravelPayloadHandler->handle($payload);
            
            return $laravelPayloadHandler;
        } catch (\Throwable $exception) {
            return \resolve(ArrayPayloadHandler::class);
        }
    }
}
