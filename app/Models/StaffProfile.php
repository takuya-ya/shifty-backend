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
        'wage',
        'max_hours_per_week',
        'min_days_per_month',
        'max_days_per_month',
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
        return $this->hasMany(Shift::class, 'staff_id', 'user_id');
    }
}
