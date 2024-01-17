<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Message;

interface MessageInterface
{
    public static function fromArray(array $data): self;

    public function toArray(): array;
}
