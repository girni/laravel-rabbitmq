<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Producer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Girni\LaravelRabbitMQ\Message\MessageInterface;

abstract class AbstractProducer implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public string $name;
    public array $data;

    public function __construct(MessageInterface $message)
    {
        $this->name = $this->name();
        $this->data = $message->toArray();
    }

    public function handle(): void
    {
    }

    abstract public function name(): string;
}
