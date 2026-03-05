<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'period_type',
        'period_start_day',
        'day_start_time',
        'day_end_time',
        'initial_view_days',
    ];
}
