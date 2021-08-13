<?php

namespace App\Listeners;

use App\Events\NotificationsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotificationsListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ExampleEvent  $event
     * @return void
     */
    public function handle(NotificationsEvent $event)
    {
        $pusher = new \Pusher\Pusher(env("PUSHER_APP_KEY"), env("PUSHER_APP_SECRET"), env("PUSHER_APP_ID"), array('cluster' => env('PUSHER_APP_CLUSTER')));

        $data = ['message' => $event->message, 'success' => $event->success];
        // print_r($data);
        $pusher->trigger('my-channel', 'NotificationEvent', $data);
    }
}
