<?php

namespace App\Http\Controllers;

use App\Helper\GenerateJWT;
use App\Models\MyStats;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $user;
    protected $myTasks;

    public function __construct(Request $request)
    {
        $token = $request->cookie('token');
        $payload = (new GenerateJWT)->decodejwt($token);

        if (gettype($payload) !== 'array') return response("Expired token", 403);

        $this->user = $payload['sub'];

        $this->myTasks = Task::where('assignee', $this->user);
    }


    public function myStats(Request $request)
    {
        $nowTime = time();
        $nowTime = date("Y-m-d h:i:s", $nowTime);

        $myStats = new MyStats();

        $myStats->total = $this->myTasks->count();
        $myStats->pendingInProgress = $this->myTasks->where('status', 'inprogress')->where('dueDate', '>', $nowTime)->count();
        $myStats->pendingNoActivity = $this->myTasks->where('status', 'pending')->where('dueDate', '>', $nowTime)->count();
        $myStats->overdueInProgress = $this->myTasks->where('status', 'inprogress')->where('dueDate', '<=', $nowTime)->count();
        $myStats->overdueNoActivity = $this->myTasks->where('status', 'pending')->where('dueDate', '<=', $nowTime)->count();
        $myStats->completedOnTime = $this->myTasks->where('status', 'completed')->whereColumn('dueDate', '>=', 'completedAt')->count();
        $myStats->completedAfterDeadline = $this->myTasks->where('status', 'completed')->whereColumn('dueDate', '<', 'completedAt')->count();

        return response()->json(['stats' => $myStats]);
    }
}
