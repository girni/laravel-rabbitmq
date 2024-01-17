<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Handler;

interface HandlerInterface
{
    public function handle(array $payload): array;
}
