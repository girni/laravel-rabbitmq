<?php

namespace Girni\LaravelRabbitMQ\Consumer;

use Illuminate\Contracts\Queue\ShouldQueue;
use Girni\LaravelRabbitMQ\Message\MessageInterface;

interface ConsumerInterface extends ShouldQueue
{
    /**
     * Represents producer name on which consumer should be fired.
     *
     * @return string
     */
    public function producer(): string;

    public function handle(MessageInterface $message): void;
}
