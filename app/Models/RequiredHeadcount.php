<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequiredHeadcount extends Model
{
    protected $fillable = [
        'day_of_week',
        'required_count',
    ];
}
