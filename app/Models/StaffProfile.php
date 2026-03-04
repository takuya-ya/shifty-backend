<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
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
