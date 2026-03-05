<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * ロール名の定数
     */
    public const ROLE_SUPER_ADMIN = 'Super Admin';
    public const ROLE_STORE_ADMIN = 'Store Admin';
    public const ROLE_STAFF = 'Staff';

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
