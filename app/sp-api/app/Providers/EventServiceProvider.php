<?php

namespace App\Providers;

use App\Events\ExampleEvent;
use App\Listeners\ExampleListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

// https://laravel.com/docs/8.x/providers
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ExampleEvent::class => [
            ExampleListener::class,
        ],
    ];
}
