<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $url;
    
    public function __construct(String $url)
    {
        $this -> url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        print_r($this -> url);
        return $this->view('emails.resetPass');
    }
}
