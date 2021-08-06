<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Queue;

class DailyReminder extends Job implements ShouldBeUnique
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $assigneesTask = Task::where('status', 'inprogress')->orWhere('status', 'pending')->distinct('assignee')->get();
        $jobs = array();

        foreach ($assigneesTask as $assigneeTask) {
            $assignee = $assigneeTask->assignee;
            $tasks = Task::where(function ($query) {
                $query->where('status', 'inprogress')
                    ->orWhere('status', 'pending');
            })
                ->where('assignee', $assignee)->get();
            $count = count($tasks);

            $subject = "Daily Task Reminder";
            $view = "emails.taskReminder";

            $job = (new SendEmail($assignee, $subject, $view, "", $tasks, $count));
            array_push($jobs, $job);
        }

        Queue::bulk($jobs);
    }
}
