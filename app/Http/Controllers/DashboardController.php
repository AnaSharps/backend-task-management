<?php

namespace App\Http\Controllers;

use App\Helper\GenerateJWT;
use App\Models\MyStats;
use App\Models\Task;
use App\Models\TodaysTasks;
use DateTime;
use Illuminate\Http\Request;

class DashboardController extends Controller
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

    public function myTasks()
    {

        $myTasks = new TodaysTasks();

        $myTasks->total = Task::where('assignee', $this->user)->count();
        $myTasks->overdue = Task::where('assignee', $this->user)->where('dueDate', '<=', $this->nowTime)->get();
        $myTasks->assigned = Task::where('assignee', $this->user)->where('dueDate', '>', $this->nowTime)->where('status', 'pending')->get();
        $myTasks->inProgress = Task::where('assignee', $this->user)->where('dueDate', '>', $this->nowTime)->where('status', 'inprogress')->get();
        $myTasks->completed = Task::where('assignee', $this->user)->where('dueDate', '>', $this->nowTime)->where('status', 'completed')->get();
        return $myTasks;
    }


    public function myStats()
    {

        $myStats = new MyStats();

        $myStats->total = Task::where('assignee', $this->user)->count();
        $myStats->pendingNoActivity = Task::where('assignee', $this->user)->where('status', 'pending')->where('dueDate', '>', $this->nowTime)->count();
        $myStats->pendingInProgress = Task::where('assignee', $this->user)->where('status', 'inprogress')->where('dueDate', '>', $this->nowTime)->count();
        $myStats->overdueNoActivity = Task::where('assignee', $this->user)->where('status', 'pending')->where('dueDate', '<=', $this->nowTime)->count();
        $myStats->overdueInProgress = Task::where('assignee', $this->user)->where('status', 'inprogress')->where('dueDate', '<=', $this->nowTime)->count();
        $myStats->completedOnTime = Task::where('assignee', $this->user)->where('status', 'completed')->whereColumn('dueDate', '>=', 'completedAt')->count();
        $myStats->completedAfterDeadline = Task::where('assignee', $this->user)->where('status', 'completed')->whereColumn('dueDate', '<', 'completedAt')->count();

        return response()->json(['stats' => $myStats]);
    }

    public function tasksForToday()
    {
        $todaysTasks = new TodaysTasks();

        $todaysTasks->total = Task::where('assignee', $this->user)->where('dueDate', '>=', $this->todaysDate)->where('dueDate', '<', $this->tomorrowsDate)->count();
        $todaysTasks->overdue = Task::where('assignee', $this->user)->where('dueDate', '>=', $this->todaysDate)->where('dueDate', '<', $this->tomorrowsDate)->where('dueDate', '<=', $this->nowTime)->get();
        $todaysTasks->inProgress = Task::where('assignee', $this->user)->where('dueDate', '>=', $this->todaysDate)->where('dueDate', '<', $this->tomorrowsDate)->where('dueDate', '>', $this->nowTime)->where('status', 'inprogress')->get();
        $todaysTasks->assigned = Task::where('assignee', $this->user)->where('dueDate', '>=', $this->todaysDate)->where('dueDate', '<', $this->tomorrowsDate)->where('dueDate', '>', $this->nowTime)->where('status', 'pending')->get();
        $todaysTasks->completed = Task::where('assignee', $this->user)->where('dueDate', '>=', $this->todaysDate)->where('dueDate', '<', $this->tomorrowsDate)->where('dueDate', '>', $this->nowTime)->where('status', 'completed')->get();

        return $todaysTasks;
    }
}
