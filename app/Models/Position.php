<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'name',
    ];

    public function staffProfiles()
    {
        return $this->belongsToMany(StaffProfile::class, 'staff_positions', 'position_id', 'staff_id');
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
