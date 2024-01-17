<?php

namespace Girni\LaravelRabbitMQ;

use Illuminate\Support\ServiceProvider;

class LaravelRabbitMQServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-rabbitmq.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-rabbitmq');

        $this->app->bind(ConsumerRegistry::class, function ($app) {
            $consumerRegistry = new ConsumerRegistry();
            $consumerRegistry->registerConsumers(\config('laravel-rabbitmq.consumers'));

            return $consumerRegistry;
        });
    }
}
