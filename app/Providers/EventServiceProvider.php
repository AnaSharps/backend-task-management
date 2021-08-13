<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ExampleEvent::class => [
            \App\Listeners\ExampleListener::class,
        ],
        \App\Events\NotificationsEvent::class => [
            \App\Listeners\NotificationsListener::class,
        ],
    ];

    // public function boot()
    // {
    //     parent::boot();
    //     //
    // }
}
