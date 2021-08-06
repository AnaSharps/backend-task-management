<?php

namespace App\Jobs;

use App\Mail\Email;
use Illuminate\Support\Facades\Mail;

class SendEmail extends Job
{
    protected $to;
    protected $subject;
    protected $view;
    protected $link;
    protected $tasks;
    protected $count;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $subject, $view, $link = "", $tasks = null, $count = null)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->view = $view;
        $this->link = $link;
        $this->tasks = $tasks;
        $this->count = $count;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->to)->send(new Email($this->link, $this->subject, $this->view, $this->tasks, $this->count));
    }
}
