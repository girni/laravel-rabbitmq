<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Consumer;

interface HasCustomMessage
{
    /**
     * @return string (FQCN of @Girni\LaravelRabbitMQ\Message\MessageInterface)
     */
    public function message(): string;
}
