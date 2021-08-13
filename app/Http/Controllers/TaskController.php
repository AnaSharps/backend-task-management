<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Jobs\SendEmail;
use App\Models\Task;
use App\Models\User;
use App\Events\NotificationsEvent;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;
use App\Models\TodaysTasks;
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

        if (gettype($payload) !== 'array') {
            event(new NotificationsEvent('Token expired', false));
            return response("Expired token", 403);
        }

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

        // $tasks = Task::where(function ($query) use ($keywords) {
        //     $query->where('taskName', 'like', $keywords)
        //         ->orWhere('taskDesc', 'like', $keywords);
        // })
        //     ->where('assignee', 'like', $assignee)
        //     ->where('dueDate', 'like', $dueDate)
        //     ->where('assignor', 'like', $assignor);

        $allTasks = new TodaysTasks();

        $allTasks->completed = Task::where(function ($query) use ($keywords) {
            $query->where('taskName', 'like', $keywords)
                ->orWhere('taskDesc', 'like', $keywords);
        })
            ->where('assignee', 'like', $assignee)
            ->where('dueDate', 'like', $dueDate)
            ->where('assignor', 'like', $assignor)->where('status', 'completed')->get();
        $allTasks->overdue = Task::where(function ($query) use ($keywords) {
            $query->where('taskName', 'like', $keywords)
                ->orWhere('taskDesc', 'like', $keywords);
        })
            ->where('assignee', 'like', $assignee)
            ->where('dueDate', 'like', $dueDate)
            ->where('assignor', 'like', $assignor)->where('dueDate', '<=', date("Y-m-d G:i:s", time()))->where('status', '!=', 'completed')->get();

        $allTasks->inProgress = Task::where(function ($query) use ($keywords) {
            $query->where('taskName', 'like', $keywords)
                ->orWhere('taskDesc', 'like', $keywords);
        })
            ->where('assignee', 'like', $assignee)
            ->where('dueDate', 'like', $dueDate)
            ->where('assignor', 'like', $assignor)->where('dueDate', '>', date("Y-m-d G:i:s", time()))->where('status', 'inprogress')->get();

        $allTasks->assigned = Task::where(function ($query) use ($keywords) {
            $query->where('taskName', 'like', $keywords)
                ->orWhere('taskDesc', 'like', $keywords);
        })
            ->where('assignee', 'like', $assignee)
            ->where('dueDate', 'like', $dueDate)
            ->where('assignor', 'like', $assignor)->where('dueDate', '>', date("Y-m-d G:i:s", time()))->where('status', 'pending')->get();

        // $count = $tasks->count();
        // $tasks = $tasks->skip($request->ofset)->take($request->display)->get();
        return response()->json(['tasks' => $allTasks]);
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
            'status' => 'required|string' ,
        ]);

        
        $id = $request->id;
        $status = $request->status;
        if ($status !== "pending" && $status !== "completed" && $status !== "inprogress") return response("Invalid Status string", 422);
        if ($status === "completed") $completedAt = date("Y-m-d G:i:s", time());
        else $completedAt = NULL;

        $task = Task::findorFail($id);
        if ($task->assignee !== $this->user && $task->assignor !== $this->user) {
            event(new NotificationsEvent('Unauthorised Request', false));
            return response("Unauthorised Request", 403);
        }

        $task->status = $status;
        $task->completedAt = $completedAt;
        if ($task->save()) {
            $subject = "Task Status Updated";
            $view = "emails.taskStatusUpdated";
            dispatch(new SendEmail([$task->assignee, $task->assignor], $subject, $view));
            event(new NotificationsEvent('Successfully updated task status!'));
            return response()->json(['status' => "Task updated Successfully", 'task' => $task]);
        }
    }

    public function deleteTask(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int',
        ]);

        $id = $request->id;

        $task = Task::findorFail($id);

        if ($task->assignor !== $this->user) {
            event(new NotificationsEvent('Unauthorised Request', false));
            return response("Unauthorised request", 403);
        }

        if ($task->delete()) {
            $subject = "Task Deleted";
            $view = "emails.deletedTask";
            dispatch(new SendEmail([$task->assignee, $task->assignor], $subject, $view));
            event(new NotificationsEvent('Successfully deleted task!'));
            return response()->json(['status' => 'Successfully deleted!']);
        }
    }

    public function createTask(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'desc' => 'required|string|max:255',
            'dueDate' => 'required|string',
            'assignee' => 'required|string|max:255',
        ]);
        $name = strtolower($request->name);
        $desc = strtolower($request->desc);
        $due = $request->dueDate;
        $assignee = strtolower($request->assignee);

        $assignedUser = User::where(['email' => strtoupper($assignee), 'isDeleted' => false])->first();
        if (!$assignedUser) {
            event(new NotificationsEvent('No such user exists', false));
            return response("No such user exists", 404);
        }
        $assignor = $this->user;
        $assignorName = User::where('email', $assignor)->where('isDeleted', false)->first()->name;

        $task = Task::where('taskName', $name)->where('taskDesc', $desc)->where('assignee', $assignee)->where('assignor', $assignor)->first();
        if ($task) {
            event(new NotificationsEvent('Task already exists', false));
            return response("Task already exists", 409);
        }

        $task = new Task();
        $task->taskName = $name;
        $task->taskDesc = $desc;
        $task->status = "pending";
        $task->dueDate = $due;
        $task->assignee = $assignee;
        $task->assignor = $assignor;
        $task->assignorName = $assignorName;

        if ($task->save()) {
            $subject = "New Task Assigned!";
            $view = "emails.newTaskAssigned";
            dispatch(new SendEmail([$assignee, $assignor], $subject, $view));
            event(new NotificationsEvent('Successfully Created Task!'));
            return response()->json(['status' => "Task created Successfully", 'task' => $task]);
        }
    }
}
