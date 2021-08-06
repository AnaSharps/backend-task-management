<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Jobs\SendEmail;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;
use Illuminate\Support\Facades\DB;

class TaskController extends AuthController
{
    protected $user;
    protected $todaysDate;
    protected $tomorrowsDate;
    protected $nowTime;

    public function __construct(Request $request)
    {
        $token = $request->cookie('token');
        $payload = (new GenerateJWT)->decodejwt($token);

        if (gettype($payload) !== 'array') return response("Expired token", 403);

        $this->user = strtolower($payload['sub']);
        $this->todaysDate = date("Y-m-d", time());
        $this->tomorrowsDate = date("Y-m-d", time() + 60 * 60 * 24);
        $this->nowTime = date("Y-m-d h:i:s", time());
    }

    public function getTasks(Request $request)
    {
        $this->validate($request, [
            'searchKeywords' => 'string|max: 255',
            'searchAssignee' => 'string|max: 255',
            'searchAssignor' => 'string|max: 255',
            'searchDueDate' => 'date_format:Y-m-d',
            'display' => 'required|int',
            'ofset' => 'required|int',
        ]);

        $keywords = "%" . $request->searchKeywords . "%";
        $assignee = "%" . $request->searchAssignee . "%";
        $assignor = "%" . $request->searchAssignor . "%";
        $dueDate = "%" . $request->searchDueDate . "%";

        $tasks = Task::where(function ($query) use ($keywords) {
            $query->where('taskName', 'like', $keywords)
                ->orWhere('taskDesc', 'like', $keywords);
        })
            ->where('assignee', 'like', $assignee)
            ->where('dueDate', 'like', $dueDate)
            ->where('assignor', 'like', $assignor);

        $count = $tasks->count();
        $tasks = $tasks->skip($request->ofset)->take($request->display)->get();
        return response()->json(['tasks' => $tasks, 'totalCount' => $count]);
    }

    public function viewTask($id)
    {
        $task = Task::findorFail($id);

        return $task;
    }

    public function updateStatus(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int',
            'status' => 'required|string',
        ]);

        $id = $request->id;
        $status = $request->status;

        $task = Task::findorFail($id);
        if ($task->assignee !== $this->user && $task->assignor !== $this->user) {
            return response("Unauthorised Request", 403);
        }

        $task->status = $status;
        if ($task->save()) {
            $subject = "Task Status Updated";
            $view = "emails.taskStatusUpdated";
            dispatch(new SendEmail([$task->assignee, $task->assignor], $subject, $view));
            return response()->json(['status' => "Task created Successfully", 'task' => $task]);
        }
        return response("some error occured", 500);
    }

    public function deleteTask(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int',
        ]);

        $id = $request->id;

        $task = Task::findorFail($id);

        if ($task->assignor !== $this->user) {
            return response("Unauthorised request", 403);
        }

        if ($task->delete()) {
            $subject = "Task Deleted";
            $view = "emails.deletedTask";
            dispatch(new SendEmail([$task->assignee, $task->assignor], $subject, $view));
            return response()->json(['status' => 'Successfully deleted!']);
        }
        return response("some error occured!", 500);
    }

    public function createTask(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'desc' => 'required|string|max:255',
            'dueDate' => 'required|int',
            'assignee' => 'required|string|max:255',
        ]);
        $name = strtolower($request->name);
        $desc = strtolower($request->desc);
        $due = $request->dueDate * 86400;
        $assignee = strtolower($request->assignee);

        $assignedUser = User::where(['email' => strtoupper($assignee), 'isDeleted' => false])->first();
        if (!$assignedUser) {
            return response("No such user exists", 400);
        }
        $assignor = $this->user;

        $task = Task::where('taskName', $name)->where('taskDesc', $desc)->where('assignee', $assignee)->where('assignor', $assignor)->first();
        if ($task) {
            return response("Task already exists", 400);
        }

        $due  = $due + time();

        $task = new Task();
        $task->taskName = $name;
        $task->taskDesc = $desc;
        $task->status = "pending";
        $task->dueDate = date("Y-m-d h:i:s", $due);
        $task->assignee = $assignee;
        $task->assignor = $assignor;

        if ($task->save()) {
            $subject = "New Task Assigned!";
            $view = "emails.newTaskAssigned";
            dispatch(new SendEmail([$assignee, $assignor], $subject, $view));
            return response()->json(['status' => "Task created Successfully", 'task' => $task]);
        }
        return response("some error occured", 500);
    }
}
