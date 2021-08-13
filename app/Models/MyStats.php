<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyStats extends Model
{
    protected $fillable = [
        'total', 'inProgress', 'noActivity', 'overdue', 'completedOnTime', 'completedAfterDeadline'
    ];
}
