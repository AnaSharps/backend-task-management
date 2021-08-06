<?php

namespace App\Mail;

use Faker\Core\Number;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Ramsey\Uuid\Type\Integer;

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
    public $tasks;
    public $taskCount;

    public function __construct(String $token, String $subject, String $view, $tasks = null, $count = null)
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
            case "Daily Task Reminder":
                print_r($count);
                $this->tasks = $tasks;
                $this->taskCount = $count;
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
        // print_r($this->url);
        print_r($this->subject);
        print_r($this->view);
        // print_r($this->tasks);
        // print_r($this->count);
        return $this->subject($this->subject)->view($this->view);
    }
}
