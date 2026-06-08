<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements AuthenticatableContract
{
    use Notifiable, SoftDeletes;

    protected $table = 'tbm_users';

    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    const DELETED_AT = 'deleted_date';

    protected $fillable = [
        'site_id', 'role_id', 'username', 'email', 'password_hash',
        'full_name', 'phone', 'is_active',
        'failed_login_attempts', 'locked_until',
        'password_changed_at', 'must_change_password',
    ];

    protected $hidden = ['password_hash', 'remember_token'];

    protected $casts = [
        'is_active'            => 'boolean',
        'must_change_password' => 'boolean',
        'last_login_at'        => 'datetime',
        'locked_until'         => 'datetime',
        'password_changed_at'  => 'datetime',
    ];

    /** Override Laravel default password field */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /* ========== Relations ========== */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /* ========== Scopes ========== */
    public function scopeOfSite(Builder $q, ?int $siteId): Builder
    {
        return $siteId === null
            ? $q->whereNull('site_id')
            : $q->where('site_id', $siteId);
    }

    /* ========== Helpers ========== */

    /** Cek permission via PG function fn_user_has_permission() */
    public function hasPermission(string $permission): bool
    {
        $result = DB::selectOne(
            'SELECT fn_user_has_permission(?, ?) AS has_perm',
            [$this->id, $permission]
        );
        return (bool) ($result->has_perm ?? false);
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function isActive(): bool
    {
        return $this->is_active && is_null($this->deleted_date);
    }

    /** Apakah super admin (site_id = NULL → akses lintas klinik) */
    public function isSuperAdmin(): bool
    {
        return is_null($this->site_id);
    }
}
