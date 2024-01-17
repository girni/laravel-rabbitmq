Laravel RabbitMQ package that extends https://github.com/vyuldashev/laravel-queue-rabbitmq to support microservice
communication.

## Installation

```bash
composer require girni/laravel-rabbitmq
```

In your `composer.json` file add these lines of codes under `require` section and `repositories`:

```json
{
  "require": {
    "girni/laravel-rabbitmq": "^1.0"
  }
}
```

After that changes simply run `composer install` or `composer update girni/laravel-rabbitmq` to make it installed.

The package will automatically register itself.

Add connection to `config/queue.php`:

```php
'connections' => [
    // ...
    'rabbitmq' => [
    
       'driver' => 'rabbitmq',
       'queue' => env('RABBITMQ_QUEUE', 'default'),
       'connection' => PhpAmqpLib\Connection\AMQPLazyConnection::class,
   
       'hosts' => [
           [
               'host' => env('RABBITMQ_HOST', '127.0.0.1'),
               'port' => env('RABBITMQ_PORT', 5672),
               'user' => env('RABBITMQ_USER', 'guest'),
               'password' => env('RABBITMQ_PASSWORD', 'guest'),
               'vhost' => env('RABBITMQ_VHOST', '/'),
           ],
       ],
   
       'options' => [
           'ssl_options' => [
               'cafile' => env('RABBITMQ_SSL_CAFILE', null),
               'local_cert' => env('RABBITMQ_SSL_LOCALCERT', null),
               'local_key' => env('RABBITMQ_SSL_LOCALKEY', null),
               'verify_peer' => env('RABBITMQ_SSL_VERIFY_PEER', true),
               'passphrase' => env('RABBITMQ_SSL_PASSPHRASE', null),
           ],
           'queue' => [
               'job' => Girni\LaravelRabbitMQ\LaravelRabbitMQJobHandler::class,
           ],
       ],
   
       /*
        * Set to "horizon" if you wish to use Laravel Horizon.
        */
       'worker' => env('RABBITMQ_WORKER', Girni\LaravelRabbitMQ\Queue\RabbitMQQueue::class),
        
    ],
    // ...    
],
```

Publish `laravel-rabbitmq.php` config file:

```bash
php artisan vendor:publish --provider="Girni\LaravelRabbitMQ\LaravelRabbitMQServiceProvider" --tag="config"
```

## Usage

## Define a queue in .env file
To ensure that our communication between applications runs smoothly, it is necessary to define separate queues for each application.
We can do it in `.env` file.

```dotenv
RABBITMQ_QUEUE=my-application-queue
```
Defining a `RABBIT_QUEUE` will result that our application will consume a messages only from that queue. By making different queues for each application we have guarantee our messages won't be processed in incorrect way by other application.

### Creating producer

Producing means nothing more than sending. A class that sends messages is a producer. Our producer classess should be as
simple as it's only possible. It should contains a message data passed by it's constructor and the `::name()` that must
be implemented due to implemented contract.

`::name()` value is very important, it's being used to recognize which consumer we should run to handle the produced
message.

```php
<?php

namespace App\Jobs\Producer;

use Girni\LaravelRabbitMQ\Message\MessageInterface;
use Girni\LaravelRabbitMQ\Producer\AbstractProducer;

class PingJobProducer extends AbstractProducer
{
    public function __construct(MessageInterface $message) 
    {        
        parent::__construct($message);
    }

    public function name(): string
    {
        return 'ping:job';
    }
}
```

### Dispatching message

To dispatch a message to a RabbitMQ we can use laravel dispatcher in several ways. More you can read here https://laravel.com/docs/9.x/queues#dispatching-jobs

```php
PingJobProducer::dispatch(\Girni\LaravelRabbitMQ\Message\BaseMessage::fromArray([
    'key1' => 'value',
    'key2' => 'value2'
]))->onQueue('my-queue')
```

It's important to define a queue on which we want to send a message by using `::onQueue()` method. 
As it's mentioned before, each application should consume the messages ONLY from its own queue. So if we want our message to be processed by another application we need to
send it to this application's queue.

#### Message structure for non Laravel sender

JSON example:

```json
{
    "job": "producer",
    "data": { "key": "value" }
}
```

### Creating a consumer

Consumer class is nothing more than a "Handler" for a producer. This class is obliged to process the data that was sent by producer.

```php 
namespace App\Jobs\Consumer;

use Girni\LaravelRabbitMQ\Consumer\ConsumerInterface;

class PingJobConsumer implements ConsumerInterface
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function producer(): string
    {
        return 'ping:job'; // this value must be same as Producer ::name() method we want to consume.
    }

    public function handle(MessageInterface $message): void
    {
        $key1 = $message->get('key1'); // value
        $key2 = $message->get('key2'); // value2
        
        \DB::table('test-table')->create(['key1' => $key1, 'key2' => $key2]);
    }
}
```

### Register consumer
In your application in `config\laravel-rabbitmq.php` file in `consumers` array you have to register each consumer you want to use in your application.
Your config should look like:

```php 
<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'consumers' => [
        \App\Jobs\Consumer\PingJobConsumer::class,
        \App\Jobs\Consumer\MyTestConsumer::class
    ]
];

```

### Consuming messages
To start consuming produced messages we need to run our queue:
```bash
php artisan rabbitmq:consume
```
or standard laravel procedure
```bash
php artisan queue:work
```

### Advanced usage - Custom Message class
As a standard message store we implemented `Girni\LaravelRabbitMQ\Message\BaseMessage::class` that has simple interface which operates on array keys.

It contains helper methods such as `::get($key), ::set($key)` to help client get/set data from/to a message.

However, in some cases our message can be very complex, and we would prefer to store its data in dedicated class (DTO) with object-oriented interface.

We can do it by adding `Girni\LaravelRabbitMQConsumer\HasCustomMessage` interface to our consumer class, that comes with a method `::message()`.
In the body of this method you should pass a FQCN to your class.

*IMPORTANT:* Make sure your DTO class implements `Girni\LaravelRabbitMQ\Message\MessageInterface`. The whole logic in which our library will create an object is hiding in `::fromArray(array $data)`
method.

```php
namespace App\Jobs\Consumer\Message;

use Girni\LaravelRabbitMQ\Message\MessageInterface;

class PingJobMessage implements MessageInterface
{
    private string $id;
    private string $name;
    private Carbon $date;

    public function __construct(string $id, string $name, Carbon $date)
    {
        $this->id = $id;
        $this->name = $name;
        $this->date = $date;
    }
    
    public  function getId(): string
    {
        return $this->id;
    }

    public  function getName(): string
    {
        return $this->name;
    }
    
    public  function getDate(): Carbon
    {
        return $this->date;
    }
    
    public static function fromArray(array $data): MessageInterface
    {
        return new self(
            $data['id'],
            $data['name'],
            Carbon::createFromFormat('Y-m-d H:i:s', $data['date'])
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'date' => $this->date
        ];
    }
}

// consumer using the message

namespace App\Jobs\Consumer;

use Girni\LaravelRabbitMQ\Consumer\ConsumerInterface;
use Girni\LaravelRabbitMQ\Consumer\HasCustomMessage;

class PingJobConsumer implements ConsumerInterface, HasCustomMessage
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function producer(): string
    {
        return 'ping:job';
    }

    /**
    * @param \App\Jobs\Consumer\Message\PingJobMessage $message
    * @return void
     */
    public function handle(MessageInterface $message): void
    {
        \DB::table('test-table')->create(['name' => $message->getName(), 'date' => $message->getDate()->format('Y-m-d')]);
    }
    
    public function message(): string
    {
        return \App\Jobs\Consumer\Message\PingJobMessage::class;
    }
}
```


### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
