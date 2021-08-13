<?php

namespace App\Http\Controllers;

use App\Events\NotificationsEvent;
use App\Helper\GenerateJWT;
use App\Models\MyStats;
use App\Models\Task;
use App\Models\TaskStats;
use App\Models\TodaysTasks;
use DateTime;
use Exception;
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

        if (gettype($payload) !== 'array') {
            event(new NotificationsEvent("Token expired", false));
            return response("Expired token", 403);
        }

        $this->user = strtolower($payload['sub']);
        $this->todaysDate = date("Y-m-d", time());
        $this->tomorrowsDate = date("Y-m-d", time() + 60 * 60 * 24);
        $this->nowTime = date("Y-m-d G:i:s", time());
    }

    public function myTasks()
    {

        $myTasks = new TodaysTasks();

        // print_r($this->nowTime);
        $myTasks->total = Task::where('assignee', $this->user)->count();
        $myTasks->overdue = Task::where('assignee', $this->user)->where('dueDate', '<=', $this->nowTime)->where('status', '!=', 'completed')->get();
        $myTasks->assigned = Task::where('assignee', $this->user)->where('dueDate', '>', $this->nowTime)->where('status', 'pending')->get();
        $myTasks->inProgress = Task::where('assignee', $this->user)->where('dueDate', '>', $this->nowTime)->where('status', 'inprogress')->get();
        $myTasks->completed = Task::where('assignee', $this->user)->where('dueDate', '>', $this->nowTime)->where('status', 'completed')->get();
        return response()->json(['tasks' => $myTasks]);
    }


    public function myStats()
    {

        $myStats = new MyStats();

        $myStats->total = Task::where('assignee', $this->user)->count();
        $myStats->noActivity = Task::where('assignee', $this->user)->where('status', 'pending')->where('dueDate', '>', $this->nowTime)->count();
        $myStats->inProgress = Task::where('assignee', $this->user)->where('status', 'inprogress')->where('dueDate', '>', $this->nowTime)->count();
        $myStats->overdue = Task::where('assignee', $this->user)->where('status', '!=', 'completed')->where('dueDate', '<=', $this->nowTime)->count();
        $myStats->completedOnTime = Task::where('assignee', $this->user)->where('status', 'completed')->whereColumn('dueDate', '>=', 'completedAt')->count();
        $myStats->completedAfterDeadline = Task::where('assignee', $this->user)->where('status', 'completed')->whereColumn('dueDate', '<', 'completedAt')->count();
        
        return response()->json(['stats' => $myStats]);
    }
    
    public function tasksForToday()
    {
        $todaysTasks = new TodaysTasks();
        
        $todaysTasks->overdue = Task::where('assignee', $this->user)->where('status', '!=', 'completed')->where('dueDate', '>=', $this->todaysDate)->where('dueDate', '<', $this->tomorrowsDate)->where('dueDate', '<=', $this->nowTime)->get();
        $todaysTasks->inProgress = Task::where('assignee', $this->user)->where('dueDate', '>=', $this->todaysDate)->where('dueDate', '<', $this->tomorrowsDate)->where('dueDate', '>', $this->nowTime)->where('status', 'inprogress')->get();
        $todaysTasks->assigned = Task::where('assignee', $this->user)->where('dueDate', '>=', $this->todaysDate)->where('dueDate', '<', $this->tomorrowsDate)->where('dueDate', '>', $this->nowTime)->where('status', 'pending')->get();
        $todaysTasks->completed = Task::where('assignee', $this->user)->where('dueDate', '>=', $this->todaysDate)->where('dueDate', '<', $this->tomorrowsDate)->where('dueDate', '>', $this->nowTime)->where('status', 'completed')->get();
        $todaysTasks->all = Task::where('assignee', $this->user)->where('dueDate', '>=', $this->todaysDate)->where('dueDate', '<', $this->tomorrowsDate)->get();
        $todaysTasks->total = count($todaysTasks->all);
        
        return response()->json(['tasks' => $todaysTasks]);
    }
    
    public function todaysStats(Request $request) {
        $this->validate($request, [
            'assignee' => 'required|string|max:255',
        ]);
        
        $todaysStats = new MyStats();
        $todaysStats->total = Task::where('assignee', $this->user)->count();
        $todaysStats->noActivity = Task::where('assignee', $this->user)->where('status', 'pending')->where('dueDate', '>', $this->nowTime)->count();
        $todaysStats->inProgress = Task::where('assignee', $this->user)->where('status', 'inprogress')->where('dueDate', '>', $this->nowTime)->count();
        $todaysStats->overdue = Task::where('assignee', $this->user)->where('status', '!=', 'completed')->where('dueDate', '<=', $this->nowTime)->count();
        $todaysStats->completedOnTime = Task::where('assignee', $this->user)->where('status', 'completed')->whereColumn('dueDate', '>=', 'completedAt')->count();

        return response()->json(['stats' => $todaysStats]);
    }

    public function taskStats(Request $request)
    {
        $this->validate($request, [
            'assignee' => 'string|max: 255',
            // 'period' => 'required|string',
        ]);

        $assignee = $request->assignee;


        if (empty($assignee)) $startingDate = Task::orderBy('dueDate')->first()->dueDate;
        else $startingDate = Task::where('assignee', $assignee)->orderBy('dueDate')->first()->dueDate;
        $startingDate = new DateTime($startingDate);
        $todaysDate = new DateTime($this->todaysDate);

        $interval = $startingDate->diff($todaysDate);

        $xPoints = $interval->m;
        $dateDiff = "-31 days";

        $dates = array();
        array_push($dates, $this->todaysDate);
        $date = $this->todaysDate;
        for ($i = 0; $i < $xPoints; $i++) {
            $date = strtotime($date);
            $date = strtotime($dateDiff, $date);
            $date = date("Y-m-d", $date);
            array_push($dates, $date);
        }

        $stats = array();

        foreach ($dates as $day) {
            $dayStats = new TaskStats();

            if (empty($assignee)) {
                $dayStats->date = $day;
                $dayStats->completedOnTime = Task::where('created_at', '<=', $day)->where('status', 'completed')->whereColumn('dueDate', '>=', 'completedAt')->count();
                $dayStats->completedAfterDeadline = Task::where('created_at', '<=', $day)->where('status', 'completed')->whereColumn('dueDate', '<', 'completedAt')->count();
                $dayStats->overdue = Task::where([['created_at', '<=', $day], ['dueDate', '<', $day], ['completedAt', '!=', null], ['completedAt', '>=', $day]])->orWhere([['created_at', '<=', $day], ['dueDate', '<', $day], ['completedAt', null]])->count();
                $dayStats->allDue = Task::where([['created_at', '<=', $day], ['dueDate', '>=', $day], ['completedAt', '!=', null], ['completedAt', '>=', $day]])->orWhere([['created_at', '<=', $day], ['dueDate', '>=', $day], ['completedAt', null]])->count();
            } else {
                $dayStats->date = $day;
                $dayStats->completedOnTime = Task::where('assignee', $assignee)->where('created_at', '<=', $day)->where('status', 'completed')->whereColumn('dueDate', '>=', 'completedAt')->count();
                $dayStats->completedAfterDeadline = Task::where('assignee', $assignee)->where('created_at', '<=', $day)->where('status', 'completed')->whereColumn('dueDate', '<', 'completedAt')->count();
                $dayStats->overdue = Task::where([['assignee', $assignee], ['created_at', '<=', $day], ['dueDate', '<', $day], ['completedAt', '!=', null], ['completedAt', '>=', $day]])->orWhere([['assignee', $assignee], ['created_at', '<=', $day], ['dueDate', '<', $day], ['completedAt', null]])->count();
                $dayStats->allDue = Task::where([['assignee', $assignee], ['created_at', '<=', $day], ['dueDate', '>=', $day], ['completedAt', '!=', null], ['completedAt', '>=', $day]])->orWhere([['assignee', $assignee], ['created_at', '<=', $day], ['dueDate', '>=', $day], ['completedAt', null]])->count();
            }
            array_push($stats, $dayStats);
        }

        return response()->json(['stats' => $stats]);
    }
}
