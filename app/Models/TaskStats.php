<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStats extends Model
{
    protected $fillable = [
        'completedOnTime', 'completedAfterDeadline', 'overdue', 'allDue', 'date'
    ];
}
