<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'deadline_days_before',
        'open_days_before',
        'send_open_notification',
        'send_deadline_remind',
        'deadline_remind_days_before',
        'updated_by',
    ];
}
