<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Message;

use Illuminate\Support\Arr;

class BaseMessage implements MessageInterface
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get(string $key)
    {
        return Arr::get($this->data, $key);
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public static function fromArray(array $data): MessageInterface
    {
        return new self($data);
    }
}
