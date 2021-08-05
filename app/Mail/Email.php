<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $url;
    public $subject;
    public $view;

    public function __construct(String $token, String $subject, String $view)
    {
        // $this->url = $url;
        $this->subject = $subject;
        $this->view = $view;
        switch ($this->subject) {
            case "Email Verification":
                $this->url = "http://localhost:3000/app/verifyEmail/?token=" . $token;
                break;
            case "Password Changed":
                $this->url = "";
                break;
            case "Successfully Registered!":
                $this->url = "";
                break;
            case "Reset Password":
                $this->url = "http://localhost:3000/resetPass/?token=" . $token;
                break;
            case "New Task Assigned!":
                $this->url = "";
                break;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // print_r($this->url);
        return $this->subject($this->subject)->view($this->view);
    }
}
