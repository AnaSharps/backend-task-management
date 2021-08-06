<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TodaysTasks extends Model
{
    protected $fillable = [
        'inProgress', 'assigned', 'overdue', 'completed', 'total'
    ];
}
