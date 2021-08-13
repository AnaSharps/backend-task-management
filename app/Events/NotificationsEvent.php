<?php

namespace App\Events;

use Exception;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class NotificationsEvent extends Event implements ShouldQueue
{
    use SerializesModels, InteractsWithSockets;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $message;
    public $success;
    
    public function __construct(String $message, bool $success = true)
    {
        // print_r($message);
        $this->message = $message;
        $this->success = $success;
    }

}
