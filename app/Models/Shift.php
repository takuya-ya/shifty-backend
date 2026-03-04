<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'staff_id',
        'position_id',
        'start_at',
        'end_at',
        'status',
        'version',
        'note',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class, 'staff_id', 'user_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
