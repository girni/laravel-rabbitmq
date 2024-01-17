<?php
declare(strict_types=1);

namespace Girni\LaravelRabbitMQ\Queue;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMQQueue extends \VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue
{
    public function __construct(
        AbstractConnection $connection,
        string $default,
        array $options = []
    ) {
        parent::__construct($connection, $default, false, $options);
    }

    /**
     * Create a AMQP message.
     *
     * @param $payload
     * @param  int  $attempts
     * @return array
     *
     * @throws JsonException
     */
    protected function createMessage($payload, int $attempts = 0): array
    {
        $properties = [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];

        $currentPayload = json_decode($payload, true, 512);
        if ($correlationId = $currentPayload['id'] ?? null) {
            $properties['correlation_id'] = $correlationId;
        }

        if ($this->isPrioritizeDelayed()) {
            $properties['priority'] = $attempts;
        }

        if (isset($currentPayload['data']['command'])) {
            $commandData = unserialize(\serialize($currentPayload['data']['command']));

            if (property_exists($commandData, 'priority')) {
                $properties['priority'] = $commandData->priority;
            }
        }

        $message = new AMQPMessage($payload, $properties);

        $message->set('application_headers', new AMQPTable([
            'laravel' => [
                'attempts' => $attempts,
            ],
        ]));

        return [
            $message,
            $correlationId,
        ];
    }
}
