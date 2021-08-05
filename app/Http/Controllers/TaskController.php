<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;

class TaskController extends AuthController
{
    public function getTasks() {
        return Task::all();
    }

    public function updateStatus(Request $request) {
        $this->validate($request, [
            'id' => 'required|int',
            'status' => 'required|string',
        ]);
        
        // try {
        $id = $request->id;
        $status = $request->status;
        $token = $request->bearerToken('token');
        $payload = (new GenerateJWT)->decodejwt($token);
        
        $task = Task::findorFail($id);
        if (gettype($payload) === 'array') {
                $user = strtolower($payload['sub']);
                // if (!$task) return response("no such task exists", 400);

                if ($task->assignee !== $user && $task->assignor !== $user) {
                    return response("Unauthorised Request", 403);
                }

                $task->status = $status;
                if ($task->save()) {
                    $subject = "Task Status Updated";
                    $view = "emails.taskStatusUpdated";
                    Mail::to([$task->assignee, $task->assignor])->send(new Email("", $subject, $view));
                    return response()->json(['status' => "Task created Successfully", 'task' => $task]);
                }
                return response("some error occured", 500);
            }
            return response("Expired token", 403);
        // } catch (Exception $e) {
        //     return response($e->getMessage(), 500);
        // }
    }

    public function deleteTask(Request $request) {
        $this->validate($request, [
            'id' => 'required|int',
        ]);

        $id = $request->id;
        $token = $request->bearerToken('token');
        $payload = (new GenerateJWT)->decodejwt($token);

        // try {
            $task = Task::findorFail($id);
            // if (!$task) return response("No such task exists", 404);

            if (gettype($payload) === 'array') {
                if ($task->assignor !== strtolower($payload['sub'])) {
                    return response("Unauthorised request", 403);
                }
                if ($task->delete()) {
                    $subject = "Task Deleted";
                    $view = "emails.deletedTask";
                    Mail::to([$task->assignee, $task->assignor])->send(new Email("", $subject, $view));
                    return response()->json(['status' => 'Successfully deleted!']);
                }
                return response("some error occured!", 500);
            }
            return response("Expired token", 403);
        // } catch (Exception $e) {
        //     return response($e->getMessage(), 500);
        // }
    }

    public function createTask(Request $request) {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'desc' => 'required|string|max:255',
            'dueDate' => 'required|int|max:255',
            'assignee' => 'required|string|max:255|regex: '+ $this->emailPattern,
        ]);

        $name = strtolower($request->name);
        $desc = strtolower($request->desc);
        $due = $request->dueDate*86400;
        $assignee = strtolower($request->assignee);

        $assignedUser = User::where(['email' => strtoupper($assignee), 'isDeleted' => false])->first();
        if (!$assignedUser) {
            return response("No such user exists", 400);
        }

        // $token = $request->cookie('token');
        $token = $request->bearerToken('token');
        $payload = (new GenerateJWT)->decodejwt($token);

        if (gettype($payload) === "array") {
            $assignor = strtolower($payload['sub']);

            $task = Task::where('deleted_at', NULL)->where('taskName', $name)->where('taskDesc', $desc)->where('assignee', $assignee)->where('assignor', $assignor)->first();
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
                    Mail::to([$assignee, $assignor])->send(new Email("", $subject, $view));
                    return response()->json(['status' => "Task created Successfully", 'task' => $task]);
                }
                return response("some error occured", 500);
        }
        return response("Expired token", 403);
    }
}
