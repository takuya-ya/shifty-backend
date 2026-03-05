<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'hourly_wage',
        'is_student',
        'date_of_birth',
        'memo',
        'max_consecutive_days',
        'max_hours_per_week',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'staff_positions', 'staff_id', 'position_id');
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'staff_id');
    }
}
