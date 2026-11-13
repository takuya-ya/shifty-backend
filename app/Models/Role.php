<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
